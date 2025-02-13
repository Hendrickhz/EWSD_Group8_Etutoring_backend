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

        return [
            'tutor_id' => $tutor->id,
            'student_id' => $student->id,
            'title' => $this->faker->sentence(6),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'type' => $this->faker->randomElement(['in-person', 'virtual']),
            'location' => $this->faker->randomElement(['Room 101', 'Room 202', 'Online']),
            'meeting_link' => $this->faker->url(),
            'notes' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}
