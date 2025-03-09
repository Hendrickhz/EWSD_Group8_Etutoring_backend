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

        $studentBlogTitles = [
            'My Recent Challenges in Learning [Subject]',
            'Feedback on My Latest Assignment',
            'What I Learned from My Last Tutoring Session',
            'Struggling with [Topic]: My Thoughts',
            'How I’m Preparing for the Upcoming Exams',
            'A Reflection on My Academic Progress',
            'Study Techniques That Work Best for Me',
            'Areas I Need to Improve In: A Self-Assessment',
            'My Goals for This Semester',
            'How My Tutor Helped Me Understand [Difficult Concept]'
        ];

        $tutorBlogTitles = [
            'How to Improve Your Critical Thinking Skills',
            'The Best Study Techniques for Better Retention',
            'Common Mistakes Students Make in [Subject]',
            'How to Prepare for Exams Without Stress',
            'Understanding [Difficult Concept] in a Simple Way',
            'Tips for Writing a Strong Research Paper',
            'Why Time Management is Crucial for Academic Success',
            'How to Ask the Right Questions During Tutoring Sessions',
            'The Importance of Consistency in Learning',
            'Key Skills You Need for Success in [Field of Study]'
        ];

        $createdAt = $this->faker->dateTimeBetween('-1 month', 'now');
        return [
            'user_id' => $author->id,
            'title' => $this->faker->randomElement($isTutor ? $tutorBlogTitles : $studentBlogTitles),
            'content' => $this->faker->paragraphs(4, true),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Blog $blog) {
            $this->generateComments($blog);

            $author = $blog->author;

            $blogCreatedAt = $blog->created_at;

            if ($author->last_active_at === null || $author->last_active_at < $blogCreatedAt) {
                $author->update([
                    'last_active_at' => $blogCreatedAt
                ]);
            }
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

