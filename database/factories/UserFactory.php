<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = $this->faker->randomElement(['staff', 'tutor', 'student']);
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $email = strtolower($firstName . '.' . $lastName . '@eduspark.edu.mm');
        return [
            'name' => "$firstName $lastName",
            'email' => $email,
            'email_verified_at' => now(),
            'role' => $role,
            'browser' => $this->faker->randomElement(['Chrome','Safari','Firefox','Edge']),
            'profile_picture' => $this->getAvatarUrl($firstName),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function getAvatarUrl($firstName)
    {
        $set = $this->faker->randomElement(['set1', 'set3', 'set4']);
        return "https://robohash.org/set_$set/" . $firstName;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
