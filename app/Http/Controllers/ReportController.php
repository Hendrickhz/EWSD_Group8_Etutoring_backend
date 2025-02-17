<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\StudentTutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getStudentsWithoutTutor()
    {
        $studentsWithoutTutor = User::where('role', 'student')
            ->whereNotIn('id', StudentTutor::pluck('student_id'))
            ->get();

        return response()->json(['studentsWithoutTutor' => $studentsWithoutTutor]);
    }

    public function getAverageMessagesPerTutor()
    {
        $tutors = User::where('role', 'tutor')->withCount('sentMessages')->get();

        $average = round($tutors->avg('sent_messages_count'));

        return response()->json(['average_messages_per_tutor' => $average]);
    }

    public function getMessagesLast7Days()
    {
        $count = Message::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return response()->json(['messages_last_7_days' => $count]);
    }
}
