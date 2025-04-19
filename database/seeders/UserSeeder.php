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
        User::factory()->create([
            'name' => 'Ella Thompson',
            'email' => 'ella.thompson@eduspark.edu.mm',
            'role' => 'staff',
            'password' => bcrypt('ella1998'),
        ]);

        // Create 3 tutors
        User::factory()->count(3)->create([
            'role' => 'tutor',
        ]);

        User::factory()->create([
            'name' => 'Liam Rodriguez',
            'email' => 'liam.rodriguez@eduspark.edu.mm',
            'role' => 'tutor',
            'password' => bcrypt('liam78rodri'),
        ]);

        User::factory()->create([
            'name' => 'Sophie Kim',
            'email' => 'sophie.kim@eduspark.edu.mm',
            'role' => 'student',
            'password' => bcrypt('sophie256'),
        ]);

        // Create 70 students
        User::factory()->count(70)->create([
            'role' => 'student',
        ]);

        $tutors = User::where('role', 'tutor')->get();
        $students = User::where('role', 'student')->get()->shuffle();

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
