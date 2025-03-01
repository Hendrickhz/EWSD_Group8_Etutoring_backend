<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogComment;
use Egulias\EmailValidator\Parser\Comment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function store(Request $request, $blog_id)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        $blog = Blog::find($blog_id);
        if (!$blog) {
            return response()->json(['error' => 'Invalid blog'], 404);
        }

        $comment = BlogComment::create([
            'blog_id' => $blog->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment
        ]);
    }

    public function index($blog_id)
    {
        $blog = Blog::find($blog_id);
        if (!$blog) {
            return response()->json(['error' => 'Invalid blog'], 404);
        }
        
        return response()->json([
            'comments' => $blog->comments()->with('user')->latest()->get()
        ]);
    }

    public function update(Request $request, $comment_id)
    {
        $user = auth()->user();

        $comment = BlogComment::find($comment_id);
        if (!$comment) {
            return response()->json(['error' => 'Invalid Comment'], 404);
        }

        if ($comment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'comment' => 'sometimes|string'
        ]);

        $comment->update($validated);

        return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment]);
    }

    public function destroy($comment_id)
    {
        $user = auth()->user();

        $comment = BlogComment::find($comment_id);
        if (!$comment) {
            return response()->json(['error' => 'Invalid Comment'], 404);
        }

        if ($comment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
