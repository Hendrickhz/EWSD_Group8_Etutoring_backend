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
        // Get all tutors
        $tutors = User::where('role', 'tutor')->get();

        // Count total messages per tutor
        $totalMessages = 0;
        $tutorCount = $tutors->count();

        foreach ($tutors as $tutor) {
            $totalMessages += Message::where('sender_id', $tutor->id)->orWhere('receiver_id', $tutor->id)->count();
        }

        // Avoid division by zero
        $average = $tutorCount > 0 ? round($totalMessages / $tutorCount) : 0;

        return response()->json([
            'average_messages_per_tutor' => $average
        ]);
    }

    public function getMessagesLast7Days()
    {
        $count = Message::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return response()->json(['messages_last_7_days' => $count]);
    }

    public function getStudentsWithNoInteraction($day)
    {
        $user = auth()->user();
        $assignedStudentIds = [];
        if ($user->role === 'tutor') {
            $assignedStudentIds = StudentTutor::where('tutor_id', $user->id)->pluck('student_id');
        }
        $studentsWithNoInteraction = User::where('role', 'student')
            ->when($user->role === 'tutor', function ($query) use ($assignedStudentIds) {
                $query->whereIn('id', $assignedStudentIds);
            })
            ->where(function ($query) use ($day) {
                $query->where('last_active_at', '<', Carbon::now()->subDays($day))
                    ->orWhereNull('last_active_at');
            })
            ->get();

        return response()->json(["students_with_no_interaction_in_{$day}days" => $studentsWithNoInteraction]);
    }

    public function getMostUsedBrowsers()
    {
        $browsers = User::select('browser', \DB::raw('count(*) as count'))
            ->whereNot('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->get();

        return response()->json(['browser_usage' => $browsers]);
    }
}
