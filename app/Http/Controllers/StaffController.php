<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function getAllStudents(){
        $students = User::where('role','student')->with('tutors')->latest()->get();

        return response()->json([
            'students' => $students
        ]);
    }

    public function getAllTutors(){
        $tutors = User::where('role','tutor')->with('students')->latest()->get();

        return response()->json([
            'tutors' => $tutors
        ]);
    }
}
