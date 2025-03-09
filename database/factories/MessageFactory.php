<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random tutor-student relationship
        $relationship = StudentTutor::inRandomOrder()->first();

        if (!$relationship) {
            return []; // Prevents inserting if no tutor-student pair exists
        }

        // Get student and tutor from the relationship
        $student = User::find($relationship->student_id);
        $tutor = User::find($relationship->tutor_id);

        if (!$student || !$tutor) {
            return []; // Skip if either is missing
        }

        // Randomly choose sender & receiver between student and tutor
        $isStudentSender = $this->faker->boolean(50);
        $sender = $isStudentSender ? $student : $tutor;
        $receiver = $isStudentSender ? $tutor : $student;

        $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');

        $messages = [
            'Hi, can we schedule a meeting this week?',
            'I need some help understanding the last lecture.',
            'Great job on your recent assignment! Keep it up!',
            'Please review my latest submission when you get a chance.',
            'Can we go over the feedback you gave me?',
            'I’m struggling with [Topic], can you explain it again?',
            'Your latest blog post was helpful, thank you!',
            'Are there any additional resources I can use for revision?',
            'Let me know when you’re available for a quick chat.',
            'Do we have any upcoming deadlines I should prepare for?'
        ];

        return [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $this->faker->randomElement($messages),
            'is_read' => false,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Message $message) {
            $sender = $message->sender;

            $messageCreatedAt = $message->created_at;

            if($sender->last_active_at === null || $sender->last_active_at < $messageCreatedAt){
                $sender->update([
                    'last_active_at' => $messageCreatedAt
                ]);
            }
        });
    }
}
