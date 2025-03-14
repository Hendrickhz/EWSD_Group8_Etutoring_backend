<?php

namespace App\Http\Controllers;

use App\Mail\MessageNotificationMail;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class MessageController extends Controller
{
    /**
     * Send Message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000'
        ]);

        $lastMessage = Message::where(function ($query) use($request){
            $query->where('receiver_id',$request->receiver_id)
            ->where('sender_id',auth()->id());
        })->orWhere(function ($query) use($request){
            $query->where('sender_id',$request->receiver_id)
            ->where('receiver_id',auth()->id());
        })->latest()->first();
        

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        $twoHoursAgo = now()->subHours(2);
        // Mail Notification to the receiver will only be sent if the last message between them is more than 2 hour ago
        if (!$lastMessage || $lastMessage->created_at->lt($twoHoursAgo)) {
            Mail::to($message->receiver->email)->send(new MessageNotificationMail($message));
        }

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
    }

    /**
     * Get Messages between the auth user and a specific user
     */
    public function getMessages($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'Invalid User'], 404);
        }

        $messages = Message::where(function ($query) use ($user_id) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $user_id);
        })
            ->orWhere(function ($query) use ($user_id) {
                $query->where('sender_id', $user_id)
                    ->where('receiver_id', auth()->id());
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    /**
     * Mark unread messages as Read
     */
    public function markAsRead($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'Invalid User'], 404);
        }

        Message::where('receiver_id', auth()->id())
            ->where('sender_id', $user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Messages marked as read successfully'
        ]);
    }

    /**
     * Get Unread Messages of the auth user
     */
    public function getUnreadMessagesCount()
    {
        $unreadMessagesCount = Message::where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unreadMessagesCount' => $unreadMessagesCount]);
    }

    /**
     * Get Unread Messages of the auth user from a specific user
     */
    public function getUnreadMessagesCountByUser($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'Invalid User'], 404);
        }
        $unreadMessagesCountByUser = Message::where('receiver_id', auth()->id())
            ->where('sender_id', $user_id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unreadMessagesCountByUser' => $unreadMessagesCountByUser]);
    }

    /**
     * Update a message
     */
    public function updateMessage(Request $request,$message_id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            return response()->json(['message' => 'Invalid Message'], 404);
        }

        if ($message->sender_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $message->update([
            'content' => $request->content
        ]);

        return response()->json(['message' => 'Message updated successfully']);
    }

    /**
     * Delete a message
     */
    public function deleteMessage($message_id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            return response()->json(['message' => 'Invalid Message'], 404);
        }
    
        if ($message->sender_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
     
        $message->delete();
    
        return response()->json(['message' => 'Message deleted successfully']);
    }
}
