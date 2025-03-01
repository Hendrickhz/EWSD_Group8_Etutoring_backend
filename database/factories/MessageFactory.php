<?php

namespace Database\Factories;

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

        return [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => $this->faker->sentence(),
            'is_read' => false,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
