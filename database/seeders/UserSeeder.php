<?php

namespace Database\Seeders;

use App\Models\StudentTutor;
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
        // Create 2 staff members
        User::factory()->count(2)->create([
            'role' => 'staff',
        ]);

        // Create 3 tutors
        User::factory()->count(3)->create([
            'role' => 'tutor',
        ]);

        // Create 70 students
        User::factory()->count(70)->create([
            'role' => 'student',
        ]);


        $tutors = User::where('role','tutor')->get();
        $students = User::where('role','student')->get()->shuffle();

        foreach ($tutors as $tutor) {
            $studentsToAssign = $students->splice(0, rand(10, 15));

            foreach ($studentsToAssign as $student) {
                StudentTutor::create([
                    'student_id' => $student->id,
                    'tutor_id' => $tutor->id,
                ]);
            }
        }
    }
}
