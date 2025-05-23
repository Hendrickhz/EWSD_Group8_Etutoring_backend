<?php

namespace App\Http\Controllers;

use App\Mail\DocumentNotificationMail;
use App\Models\Document;
use App\Models\StudentTutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role === 'student') {
            $tutor_id = StudentTutor::where('student_id', $user->id)->value('tutor_id');
            $documents = Document::with('user', 'comments')
                ->where('user_id', $user->id)
                ->orWhere('user_id', $tutor_id)
                ->orderByDesc('created_at')
                ->get();
        } elseif ($user->role === 'tutor') {
            $documents = Document::with('user', 'comments')
                ->where('user_id', $user->id)
                ->orWhereIn('user_id', StudentTutor::where('tutor_id', $user->id)->pluck('student_id'))
                ->orderByDesc('created_at')
                ->get();
        } else {
            $documents = Document::with('user', 'comments')
                ->orderByDesc('created_at')
                ->get();
        }
    
        // Transform documents to include file type and file size
        $documents->transform(function ($document) {
            $filePath = storage_path("app/public/{$document->path}");
            $fileType = file_exists($filePath) ? mime_content_type($filePath) : null;
            $fileSize = file_exists($filePath) ? round(filesize($filePath) / 1048576, 2) : null; // Shows in megabytes
    
            return [
                'id' => $document->id,
                'user_id' => $document->user_id,
                'filename' => $document->filename,
                'title' => $document->title,
                'description' => $document->description,
                'path' => $document->path,
                'file_url' => asset("storage/{$document->path}"),
                'file_type' => $fileType,
                'file_size_mb' => $fileSize,
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,
                'user' => $document->user,
                'comments' => $document->comments,
            ];
        });
    
        return response()->json(['documents' => $documents]);
    }

    public function upload(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,jpg,png|max:5120',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);
    
        if (!in_array($user->role, ['student', 'tutor'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $file = $request->file('file');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = Str::slug($originalName, '_');
        $extension = $file->getClientOriginalExtension();
        $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
        $path = $file->storeAs("documents/{$user->id}", $filename, 'public');
    
        $document = Document::create([
            'user_id' => $user->id,
            'filename' => $filename,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'path' => $path,
        ]);
    
        // Get file properties
        $filePath = storage_path("app/public/{$path}");
        $fileType = file_exists($filePath) ? mime_content_type($filePath) : null;
        $fileSize = file_exists($filePath) ? round(filesize($filePath) / 1048576, 2) : null; //Shows in megabytes
    
        $recipients = [];
        if ($user->role === 'student') {
            $tutor_id = StudentTutor::where('student_id', $user->id)->value('tutor_id');
            if ($tutor_id) {
                $recipients[] = User::find($tutor_id)->email;
            }
        } elseif ($user->role === 'tutor') {
            $student_ids = StudentTutor::where('tutor_id', $user->id)->pluck('student_id');
            $recipients = User::whereIn('id', $student_ids)->pluck('email')->toArray();
        }
    
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new DocumentNotificationMail($document, $user));
        }
    
        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $document,
            'file_url' => asset("storage/{$path}"),
            'file_type' => $fileType,
            'file_size_mb' => $fileSize, // File size in MB
        ], 201);
    }


    public function show($id)
    {
        $document = Document::with('user')->find($id);
        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }
    
        $filePath = storage_path("app/public/{$document->path}");
        $fileType = file_exists($filePath) ? mime_content_type($filePath) : null;
        $fileSize = file_exists($filePath) ? round(filesize($filePath) / 1048576, 2) : null;
    
        return response()->json([
            'document' => $document,
            'user' => $document->user,
            'file_type' => $fileType,
            'file_size_mb' => $fileSize,
            'file_url' => asset("storage/{$document->path}")
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $document = Document::find($id);
    
        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }
    
        if ($document->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized, only the publisher of the document can edit'], 403);
        }
    
        $request->validate([
            'file' => 'nullable|file|mimes:pdf,docx,jpg,png|max:5120',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);
    
        $document->title = $request->input('title');
        $document->description = $request->input('description');
    
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($document->path && Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
    
            $file = $request->file('file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $originalName = Str::slug($originalName, '_');
            $extension = $file->getClientOriginalExtension();
            $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs("documents/{$user->id}", $filename, 'public');
    
            $document->filename = $filename;
            $document->path = $path;
        }
    
        $document->save();
    
 
        $filePath = storage_path("app/public/{$document->path}");
        $fileType = file_exists($filePath) ? mime_content_type($filePath) : null;
        $fileSize = file_exists($filePath) ? round(filesize($filePath) / 1048576, 2) : null; // File size in MB
    
        return response()->json([
            'message' => 'Document updated successfully',
            'document' => $document,
            'file_url' => asset("storage/{$document->path}"),
            'file_type' => $fileType,
            'file_size_mb' => $fileSize,
        ], 200);
    }

    public function delete($id) //deletes document both in database and public storage
    {
        $user = auth()->user();
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        if ($document->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized, only the publisher of the document can delete'], 403);
        }

        $filePath = storage_path('app/public/' . $document->path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }

    public function getDocumentsByUserId(Request $request, $user_id)
    {
        $user = User::find($user_id);
    
        if (!$user) {
            return response()->json(['error' => 'Invalid User'], 404);
        }
    
        $documents = Document::with('user', 'comments')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    
        $documents->transform(function ($document) {
            $filePath = storage_path("app/public/{$document->path}");
            $fileType = file_exists($filePath) ? mime_content_type($filePath) : null;
            $fileSize = file_exists($filePath) ? round(filesize($filePath) / 1048576, 2) : null; // Shows in megabytes
    
            return [
                'id' => $document->id,
                'user_id' => $document->user_id,
                'filename' => $document->filename,
                'title' => $document->title,
                'description' => $document->description,
                'path' => $document->path,
                'file_url' => asset("storage/{$document->path}"),
                'file_type' => $fileType,
                'file_size_mb' => $fileSize,
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,
                'user' => $document->user,
                'comments' => $document->comments,
            ];
        });
    
        return response()->json(['documents' => $documents]);
    }


    public function viewTutorsDocuments() //view all teachers uploaded documents 
    {
        $user = auth()->user();
        if ($user->role !== 'staff') {
            return response()->json(['error' => 'Unauthorized, only staff can view tutor documents'], 403);
        }

        $documents = Document::whereIn('user_id', User::where('role', 'tutor')->pluck('id'))
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['documents' => $documents]);
    }

    public function viewStudentsDocuments() //view all students uploaded documents 
    {
        $user = auth()->user();
        if ($user->role !== 'staff') {
            return response()->json(['error' => 'Unauthorized, only staff can view student documents'], 403);
        }

        $documents = Document::whereIn('user_id', User::where('role', 'student')->pluck('id'))
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['documents' => $documents]);
    }

    public function getAssignedStudentsDocuments($tutorId)
    {
        $user = auth()->user();
        if ($user->role !== 'tutor' || $user->id !== (int) $tutorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $students = StudentTutor::where('tutor_id', $tutorId)->pluck('student_id');

        $documents = Document::whereIn('user_id', $students)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();


        return response()->json(['documents' => $documents]);
    }
}
