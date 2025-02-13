<?php

namespace App\Http\Controllers;

use App\Mail\MeetingNotificationMail;
use App\Models\Meeting;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class MeetingController extends Controller
{
    /**
     * Create a meeting for a student
     */
    public function createMeeting(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'type' => 'required|in:in-person,virtual',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'date' => 'required|string',
            'time' => 'required|string',
        ]);

        if ($request->user()->role !== 'tutor' || !$this->isAssigned($request->user()->id, $request->student_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $student = User::find($request->student_id);

        $meeting = Meeting::create([
            'tutor_id' => $request->user()->id,
            // 'tutor_id' => 4,
            'student_id' => $request->student_id,
            'title' => $request->title,
            'notes' => $request->notes,
            'type' => $request->type,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'date' => $request->date,
            'time' => $request->time,
            'status' => 'confirmed'
        ]);

        Mail::to($student->email)->send(new MeetingNotificationMail($meeting, "A new meeting is scheduled."));

        return response()->json([
            'message' => 'A new meeting is scheduled',
            'meeting' => $meeting
        ]);
    }

    /**
     * Request a meeting with the assigned tutor
     */
    public function requestMeeting(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'type' => 'required|in:in-person,virtual',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'date' => 'required|string',
            'time' => 'required|string',
        ]);

        $student = $request->user();
        if ($student->role !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor_id = StudentTutor::where('student_id', $student->id)->value('tutor_id');
        if (!$tutor_id) {
            return response()->json(['message' => 'No assigned tutor found'], 404);
        }
        $tutor = User::find($tutor_id);

        $meeting = Meeting::create([
            'tutor_id' => $tutor_id,
            'student_id' => $request->user()->id,
            // 'tutor_id' => 4,
            // 'student_id' => 25,
            'title' => $request->title,
            'notes' => $request->notes,
            'type' => $request->type,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'date' => $request->date,
            'time' => $request->time,
            'status' => 'pending'
        ]);

        Mail::to($tutor->email)->send(new MeetingNotificationMail($meeting, "A new meeting is requested."));

        return response()->json([
            'message' => 'A new meeting is requested',
            'meeting' => $meeting
        ]);

    }

    /**
     * Update the meeting information by the tutor
     */
    public function updateMeeting(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
            'type' => 'sometimes|in:in-person,virtual',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'date' => 'sometimes|string',
            'time' => 'sometimes|string',
            'status' => 'sometimes|in:pending,confirmed,cancelled',
        ]);

        $meeting = Meeting::find($id);
        if (!$meeting) {
            return response()->json(['message' => 'Meeting not found'], 404);
        }

        if ($request->user()->id !== $meeting->tutor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $meeting->update($request->only([
            'title',
            'notes',
            'type',
            'location',
            'meeting_link',
            'time',
            'date',
            'status'
        ]));

        $student = User::find($meeting->student_id);

        Mail::to($student->email)->send(new MeetingNotificationMail($meeting, "The meeting is updated."));
        return response()->json(['message' => 'Meeting updated successfully', 'meeting' => $meeting]);
    }

    /**
     * Get the meetings for the student
     */
    public function getStudentMeetings(Request $request)
    {
        $student = $request->user();

        if ($student->role !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $meetings = Meeting::where('student_id', $student->id)->with('tutor')->latest()->get();
        // $meetings = Meeting::where('student_id',12)->with('tutor')->get();

        return response()->json(['meetings' => $meetings]);
    }

    /**
     * Get the meetings for the tutor
     */
    public function getTutorMeetings(Request $request)
    {
        $tutor = $request->user();

        if ($tutor->role !== 'tutor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // $meetings = Meeting::where('tutor_id',4)->with('student')->get();
        $meetings = Meeting::where('tutor_id', $tutor->id)->with('student')->latest()->get();

        return response()->json(['meetings' => $meetings]);
    }

    /**
     * Get the all the meetings for the staff
     */
    public function getAllMeetings(Request $request)
    {
        $staff = $request->user();

        if ($staff->role !== 'staff') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $meetings = Meeting::with('student', 'tutor')->orderBy('date', 'desc')->get();

        return response()->json(['meetings' => $meetings]);
    }

    /**
     * Get the all the meetings of a specific tutor for the staff
     */
    public function getTutorMeetingsForStaff(Request $request, $tutorId)
    {
        $staff = $request->user();

        if ($staff->role !== 'staff') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor = User::find($tutorId);
        if ($tutor->role !== 'tutor') {
            return response()->json(['message' => 'Invalid tutor'], 404);
        }

        // $meetings = Meeting::where('tutor_id',4)->with('student')->orderBy('date','desc')->get();
        $meetings = Meeting::where('tutor_id', $tutorId)->with('student')->orderBy('date', 'desc')->get();

        return response()->json(['meetings' => $meetings]);
    }

    /**
     * Get the all the meetings of a specific student for the staff
     */
    public function getStudentMeetingsForStaff(Request $request, $studentId)
    {
        $staff = $request->user();

        if ($staff->role !== 'staff') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = User::find($studentId);
        if($student->role !== 'student'){
            return response()->json(['message'=>'Invalid student'],404);
        }

        // $meetings = Meeting::where('student_id',12)->with('tutor')->orderBy('date','desc')->get();
        $meetings = Meeting::where('student_id', $studentId)->with('tutor')->orderBy('date', 'desc')->get();

        return response()->json(['meetings' => $meetings]);
    }

    /**
     * Helper function to check if tutor is assigned to the student
     */
    public function isAssigned($tutor_id, $student_id)
    {
        return StudentTutor::where('tutor_id', $tutor_id)->where('student_id', $student_id)->exists();
    }
}
