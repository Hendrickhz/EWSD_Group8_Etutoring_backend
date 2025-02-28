<?php 
namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        $user = User::inRandomOrder()->first();
        $extension = $this->faker->randomElement(['pdf', 'docx', 'jpg', 'png']);
        $filename = $this->faker->word . '.' . $extension;
        $path = "documents/{$user->id}/{$filename}";

        return [
            'user_id'  => $user->id,
            'filename' => $filename,
            'title'      => $this->faker->sentence,  
            'description'=> $this->faker->optional()->text(500),
            'path'     => $path
        ];
    }




































    public function configure()
    {
        return $this->afterCreating(function (Document $document) {
            $this->generateDocumentComments($document);  // Call our new method to generate comments
        });
    }

    /**
     * Generates comments for a given document.
     *
     * @param \App\Models\Document $document
     */
    private function generateDocumentComments(Document $document)
    {
        // Retrieve the author of the document
        $author = $document->user; // The user who created the document (could be a tutor or student)
        $commenters = [];

        // If the document is for a specific student (not public), only assigned students or tutors can comment
        if ($author->role === 'tutor') {
            // If the document is created by a tutor, only the students assigned to this tutor should comment
            $assignedStudents = StudentTutor::where('tutor_id', $author->id)->pluck('student_id')->toArray();
            $commenters = User::whereIn('id', $assignedStudents)->get();
        } elseif ($author->role === 'student') {
            // If the document was written by a student, only the tutor can comment
            $tutor = StudentTutor::where('student_id', $author->id)->first()?->tutor;
            if ($tutor) {
                $commenters[] = $tutor;  // Only add the tutor as the commenter
            }
        }

        // Now, create comments from the available commenters (50% chance each commenter leaves a comment)
        foreach ($commenters as $commenter) {
            if (rand(0, 1)) { // 50% chance the commenter will leave a comment
                DocumentComment::create([
                    'document_id' => $document->id,
                    'user_id' => $commenter->id,
                    'comment' => $this->faker->sentence(),  // Random comment
                ]);
            }
        }
    }
}