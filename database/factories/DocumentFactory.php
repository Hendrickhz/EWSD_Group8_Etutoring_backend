<?php 
namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        $user = User::whereNot('role','staff')->inRandomOrder()->first();
        $extension = $this->faker->randomElement(['pdf', 'png']);
        $filename = $this->faker->word . '.' . $extension;

        $directory = "documents/{$user->id}";
        Storage::disk('public')->makeDirectory($directory);
        $dummyFilePath = resource_path("dummy_files/sample.{$extension}");
        if (!file_exists($dummyFilePath)) {
            file_put_contents($dummyFilePath, 'This is a dummy file for testing.');
        }
        // Store the dummy file in storage
        $storedPath = Storage::disk('public')->putFileAs($directory, new File($dummyFilePath), $filename);
        $createdAt = $this->faker->dateTimeBetween('-1 month', 'now');
        return [
            'user_id'  => $user->id,
            'filename' => $filename,
            'title'      => $this->faker->sentence,  
            'description'=> $this->faker->optional()->text(500),
            'path'     => $storedPath,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Document $document) {
            $this->generateDocumentComments($document);  // Call our new method to generate comments

            $user = $document->user;

            $documentCreatedAt = $document->created_at;

            if($user->last_active_at === null || $user->last_active_at < $documentCreatedAt){
                $user->update([
                    'last_active_at' => $documentCreatedAt
                ]);
            }
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
        $author = $document->user;
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