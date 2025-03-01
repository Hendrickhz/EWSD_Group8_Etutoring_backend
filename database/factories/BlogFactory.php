<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition()
    {
        // 30% chance the author is a tutor, otherwise a student
        $isTutor = $this->faker->boolean(30);
        $author = User::where('role', $isTutor ? 'tutor' : 'student')->inRandomOrder()->first();

        // $student_id = null;
        // if ($author->role === 'tutor') {
        //     $assigned_students = StudentTutor::where('tutor_id', $author->id)->pluck('student_id')->toArray();
        //     if (!empty($assigned_students) && rand(0, 1)) {
        //         $student_id = $this->faker->randomElement($assigned_students);
        //     }
        // }

        return [
            'user_id' => $author->id,
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(4, true),
            // 'student_id' => $student_id,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Blog $blog) {
            $this->generateComments($blog);
        });
    }

    private function generateComments(Blog $blog)
    {
        $commenters = [];

        if ($blog->students->isNotEmpty()) {
            // If the blog is for specific students, only those students can comment
            $commenters = $blog->students->all();
        } else {
            // If no student is specified (public blog), all assigned students of the tutor can comment
            if ($blog->author->role === 'tutor') {
                $assigned_students = StudentTutor::where('tutor_id', $blog->user_id)->pluck('student_id')->toArray();
                $commenters = User::whereIn('id', $assigned_students)->get()->all();
            }
        }

        if ($blog->author->role === 'student') {
            // If the blog was written by a student, only their assigned tutor can comment
            $tutor = StudentTutor::where('student_id', $blog->user_id)->first()?->tutor;
            if ($tutor) {
                $commenters[] = $tutor;
            }
        }

        // Filter out null values and randomize commenting
        $commenters = array_filter($commenters);
        foreach ($commenters as $commenter) {
            if (rand(0, 1)) { // 50% chance a commenter will leave a comment
                BlogComment::create([
                    'blog_id' => $blog->id,
                    'user_id' => $commenter->id,
                    'comment' => $this->faker->sentence(),
                ]);
            }
        }
    }

}

