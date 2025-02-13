<?php

namespace App\Http\Controllers;

use App\Mail\TutorAssignmentMail;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class AllocationController extends Controller
{

    /**
     * Allocate a single student to a tutor
     */
    public function allocateStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'tutor_id' => 'required|exists:users,id'
        ]);

        $student = User::find($request->student_id);
        $tutor = User::find($request->tutor_id);

        if ($student->role !== 'student' || $tutor->role !== 'tutor') {
            return response()->json(['error' => 'Invalid student or tutor role.'], 400);
        }

        // Check if tutor already has 20 students
        $studentCount = StudentTutor::where('tutor_id', $tutor->id)->count();
        if ($studentCount >= 20) {
            return response()->json(['error' => 'This tutor already has 20 students'], 400);
        }

        // Check if student already has a tutor, update if exists
        $existingAllocation = StudentTutor::where('student_id', $request->student_id)->first();

        if ($existingAllocation) {

            $existingAllocation->update([
                'tutor_id' => $request->tutor_id
            ]);

            //Send email to the student
            Mail::to($student->email)->send(new TutorAssignmentMail($student, $tutor, 'student'));
            // Send email to the tutor
            Mail::to($tutor->email)->send(new TutorAssignmentMail($tutor, $student, 'tutor'));

            return response()->json(['message' => 'Student reallocated sucessfully.']);
        } else {

            StudentTutor::create([
                'student_id' => $request->student_id,
                'tutor_id' => $request->tutor_id
            ]);

            //Send email to the student
            Mail::to($student->email)->send(new TutorAssignmentMail($student, $tutor, 'student'));
            // Send email to the tutor
            Mail::to($tutor->email)->send(new TutorAssignmentMail($tutor, $student, 'tutor'));

            return response()->json(['message' => 'Student allocated successfully.']);
        }
    }

    /**
     * Bulk allocate students to a tutor
     */
    public function bulkAllocate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'tutor_id' => 'required|exists:users,id',
        ]);

        $tutor = User::find($request->tutor_id);

        if ($tutor->role !== 'tutor') {
            return response()->json(['error' => 'Invalid tutor'], 400);
        }

        // Count current students assigned to the tutor
        $currentStudentsCount = StudentTutor::where('tutor_id', $tutor->id)->count();

        // Check if the bulk allocation exceeds the maximum student limit
        if (($currentStudentsCount + count($request->student_ids)) > 20) {
            return response()->json([
                'message' => "Cannot allocate students. This tutor already has $currentStudentsCount students and can only accept " . (20 - $currentStudentsCount) . " more."
            ], 400);
        }

        foreach ($request->student_ids as $student_id) {
            $student = User::find($student_id);
            if ($student->role !== 'student') {
                continue; //Skip Invalid entries
            }

            //Check if student already has a tutor
            $existingAllocation = StudentTutor::where('student_id', $student_id)->first();

            if ($existingAllocation) {
                $existingAllocation->update(['tutor_id' => $request->tutor_id]);
            } else {
                StudentTutor::create([
                    'student_id' => $student_id,
                    'tutor_id' => $request->tutor_id
                ]);
            }

            //Send email to the student
            Mail::to($student->email)->send(new TutorAssignmentMail($student, $tutor, 'student'));
            // Send email to the tutor
            Mail::to($tutor->email)->send(new TutorAssignmentMail($tutor, $student, 'tutor'));
        }
        return response()->json(['message' => 'Bulk allocation completed successfully.']);
    }

    /**
     * Get list of students assigned to a tutor
     */
    public function getTutorStudents($tutor_id)
    {
        $students = StudentTutor::where('tutor_id', $tutor_id)->with('student')->get();

        return response()->json([
            'tutor_id' => $tutor_id,
            'students' => $students
        ], 200);
    }

    /**
     * Remove the assigned tutor from the student
     */
    public function removeTutorFromStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $deleted = StudentTutor::where('student_id', $request->student_id)->delete();

        if ($deleted) {
            return response()->json(['message' => 'Tutor removed from the student successfully.']);
        } else {
            return response()->json(['message' => 'No Tutor found for this student.'], 404);
        }
    }

    /**
     * Get the assigned tutor info for the login student
     */
    public function getTutorInfoForStudent()
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $tutor = User::find($user->tutors->tutor_id);
        return response()->json(['tutor' => $tutor]);
    }
}
