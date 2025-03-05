<?php

namespace App\Http\Controllers;

use App\Mail\RemoveAllocationMail;
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

        $existingAssignment = StudentTutor::where('student_id', $student->id)
            ->where('tutor_id', $tutor->id)
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'This student is already assigned to this tutor. No changes made.'
            ]);
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

        // Get currently assigned students
        $currentStudents = StudentTutor::where('tutor_id', $tutor->id)->pluck('student_id')->toArray();
        $currentCount = count($currentStudents);

        // Filter out students who are already assigned to this tutor
        $newStudentIds = array_diff($request->student_ids, $currentStudents);
        $newStudentsCount = count($newStudentIds);

        // Maximum student limit per tutor
        $maxStudents = 20;
        $remainingSlots = $maxStudents - $currentCount;

        // If there is no new student to assign
        if ($newStudentsCount === 0) {
            return response()->json([
                'message' => "All the selected students are already assigned to this tutor."
            ], 200);
        }

        // If the tutor is already full
        if ($remainingSlots <= 0) {
            return response()->json([
                'error' => "Cannot allocate students. This tutor already has $currentCount students and cannot be allocated anymore."
            ], 400);
        }

        // If the number of new students exceeds available slots
        if ($newStudentsCount > $remainingSlots) {
            return response()->json([
                'error' => "Cannot allocate students. This tutor already has $currentCount students and can only accept $remainingSlots more."
            ], 400);
        }

        foreach ($newStudentIds as $student_id) {
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
     * Remove the assigned tutor from the student
     */
    public function removeTutorFromStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);
        $student = User::find($request->student_id);

        $studentTutor = StudentTutor::where('student_id', $student->id)->first();
    
        if (!$studentTutor) {
            return response()->json(['message' => 'No Tutor found for this student.'], 404);
        }
    
        $tutor = User::find($studentTutor->tutor_id);
    
        $deleted = $studentTutor->delete();

        if ($deleted) {
             //Send email to the student
             Mail::to($student->email)->send(new RemoveAllocationMail($student, $tutor, 'student'));
             // Send email to the tutor
             Mail::to($tutor->email)->send(new RemoveAllocationMail($tutor, $student, 'tutor'));
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

    /**
     * Get the list of students assigned for logged in tutor
     */
    public function getStudentsInfoTutor()
    {
        $user = auth()->user();
        if ($user->role !== 'tutor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $students = User::whereHas('tutors', function ($query) use ($user) {
            $query->where('tutor_id', $user->id);
        })->select('id', 'name', 'email', 'profile_picture', 'last_login', 'created_at')
            ->get();

        return response()->json([
            'students' => $students
        ], 200);
    }

    /**
     * Get the list of students assigned for given tutor id
     */
    public function getStudentsInfoByTutorId($tutor_id)
    {
        $tutor = User::find($tutor_id);

        if (!$tutor || $tutor->role !== 'tutor') {
            return response()->json(['message' => 'Invalid tutor'], 404);
        }

        return response()->json(['assignedStudents' => $tutor->students]);
    }

    /**
     * Get the assigned tutor info for given tutor id
     */
    public function getTutorInfoByStudentId($student_id)
    {
        $student = User::find($student_id);

        if (!$student || $student->role !== 'student') {
            return response()->json(['message' => 'Invalid Student'], 404);
        }

        return response()->json(['assignedTutor' => $student->tutor]);
    }
}
