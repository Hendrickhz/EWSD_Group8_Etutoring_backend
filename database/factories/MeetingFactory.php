<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random tutor and one of their assigned students
        $tutor = User::where('role', 'tutor')->inRandomOrder()->first();
        $student = User::where('role', 'student')
            ->whereHas('tutors', function ($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id);
            })
            ->inRandomOrder()
            ->first();

        $type = $this->faker->randomElement(['in-person', 'virtual']);
        $location = null;
        $platform = null;
        $meeting_link = null;
        if ($type === 'in-person') {
            $location = $this->faker->randomElement(['Room 101', 'Room 202', 'Jupiter Room']);
        } else {
            $platform = $this->faker->randomElement(['Zoom', 'Teams', 'Google Meet']);
            $meeting_link = $this->faker->url();
        }

        $titles = [
            'One-on-One Tutoring Session',
            'Weekly Progress Review',
            'Project Discussion Meeting',
            'Exam Preparation Session',
            'Assignment Review Meeting',
            'Feedback and Guidance Session',
            'Career Counseling Meeting',
            'Lab Report Discussion',
            'Final Year Project Planning'
        ];

        return [
            'tutor_id' => $tutor->id,
            'student_id' => $student->id,
            'title' => $this->faker->randomElement($titles),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'type' => $type,
            'location' => $location,
            'platform' => $platform,
            'meeting_link' => $meeting_link,
            'notes' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}
