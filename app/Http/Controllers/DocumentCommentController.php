<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentComment;
use Illuminate\Http\Request;

class DocumentCommentController extends Controller
{

    public function storeDocumentComment(Request $request, $document_id)
    {
        $request->validate([
            'comment' => 'required|string' 
        ]);
    
        $document = Document::find($document_id);
        if (!$document) {
            return response()->json(['error' => 'Invalid document'], 404);
        }
    
        $comment = DocumentComment::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment 
        ]);
    
        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment
        ]);
    }

   
    public function getDocumentComments($document_id) //get comments of each document
    {
        $document = Document::find($document_id);
        if (!$document) {
            return response()->json(['error' => 'Invalid document'], 404);
        }

        return response()->json([
            'comments' => $document->comments()->with('user')->latest()->get()
        ]);
    }


    public function updateDocumentComment(Request $request, $comment_id) //update document comments
    {
        $user = auth()->user();

        $comment = DocumentComment::find($comment_id);
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

  
    public function deleteDocumentComment($comment_id) //delete document comments
    {
        $user = auth()->user();

        $comment = DocumentComment::find($comment_id);
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