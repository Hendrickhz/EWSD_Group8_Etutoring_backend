<?php

namespace App\Http\Controllers;

use App\Mail\BlogNotificationMail;
use App\Models\Blog;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'student') {
            $tutor_id = StudentTutor::where('student_id', $user->id)->value('tutor_id');
            // get blogs written by the student or his assigned tutor
            $blogs = Blog::with('author','comments')->where('user_id', $user->id)
                ->orWhere(function ($query) use ($tutor_id, $user) {
                    $query->where('user_id', $tutor_id)
                        ->where(function ($q) use ($user) {
                            $q->where('student_id', $user->id)
                                ->orWhereNull('student_id');
                        });
                })
                ->orderByDesc('created_at')
                ->get();
        } elseif ($user->role === 'tutor') {
            $blogs = Blog::with('author','comments')->where('user_id', $user->id)
                ->orWhereIn('student_id', StudentTutor::where('tutor_id', $user->id)->pluck('student_id'))
                ->orderByDesc('created_at')
                ->get();

        } else {
            $blogs = Blog::with('author','comments')->orderByDesc('created_at')->get();
        }

        return response()->json(['blogs' => $blogs]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'student_id' => 'nullable|exists:users,id'
        ]);

        $blog = Blog::create([
            'user_id' => auth()->id(),
            'student_id' => $request->student_id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $recipients = [];

        if ($user->role === 'student') {
            $tutor_id = StudentTutor::where('student_id', $user->id)->value('tutor_id');
            if ($tutor_id) {
                $recipients[] = User::find($tutor_id)->email;
            }
        } elseif ($user->role === 'tutor') {
            // Send to specific student or all assigned students
            if ($blog->student_id) {
                $recipients[] = User::find($blog->student_id)->email;
            } else {
                $student_ids = StudentTutor::where('tutor_id', $user->id)->pluck('student_id');
                $recipients = User::whereIn('id', $student_ids)->pluck('email')->toArray();
            }
        }
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new BlogNotificationMail($blog, "New Blog Post from {$user->name}"));
        }

        return response()->json(['message' => 'Blog created successfully.', 'blog' => $blog]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $blog = Blog::with('author', 'comments')->find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        return response()->json(['blog' => $blog]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $blog_id)
    {
        $user = auth()->user();

        $blog = Blog::find($blog_id);
        if (!$blog) {
            return response()->json(['error' => 'Invalid blog'], 404);
        }

        if ($blog->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string'
        ]);

        $blog->update($validated);

        return response()->json(['message' => 'Blog updated successfully', 'blog' => $blog]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($blog_id)
    {
        $user = auth()->user();

        $blog = Blog::find($blog_id);
        if (!$blog) {
            return response()->json(['error' => 'Invalid blog'], 404);
        }

        if ($blog->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully']);
    }

    public function getBlogsByUser(Request $request, $user_id)
    {

        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['error' => 'Invalid User'], 404);
        }
        $blogs = Blog::where('user_id', $user->id)->with('author','comments')->latest()->get();

        return response()->json(['blogs' => $blogs]);
    }
}
