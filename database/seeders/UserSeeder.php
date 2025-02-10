<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Function to generate random profile pictures
        function getRandomProfilePicture($role, $index)
        {
            return "https://i.pravatar.cc/150?img=" . ($index % 70); // Generates a random avatar
        }

        // Create Staff Users
        for ($i = 1; $i <= 2; $i++) {
            User::create([
                'name' => "Admin Staff $i",
                'email' => "staff$i@example.com",
                'role' => 'staff',
                'password' => Hash::make('password123'),
                'profile_picture' => getRandomProfilePicture('staff', $i)
            ]);
        }

        // Create Tutor Users
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Tutor $i",
                'email' => "tutor$i@example.com",
                'role' => 'tutor',
                'password' => Hash::make('password123'),
                'profile_picture' => getRandomProfilePicture('tutor', $i)
            ]);
        }

        // Create 40 Student Users
        for ($i = 1; $i <= 40; $i++) {
            User::create([
                'name' => "Student $i",
                'email' => "student$i@example.com",
                'role' => 'student',
                'password' => Hash::make('password123'),
                'profile_picture' => getRandomProfilePicture('student', $i)
            ]);
        }
    }
}
