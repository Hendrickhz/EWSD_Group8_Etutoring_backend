<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DocumentCommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;
use App\Http\Middleware\StaffOnly;
use App\Http\Middleware\UpdateLastActive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use Jenssegers\Agent\Agent;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {

    // For Staff
    Route::controller(StaffController::class)->middleware(StaffOnly::class)->prefix('staff')->group(function () {
        Route::get('/get-all-students', 'getAllStudents'); //get all student information
        Route::get('/get-all-tutors', 'getAllTutors'); //get all tutor information
    });

    // Allocation, Reallocation Managed by Staff only
    Route::controller(AllocationController::class)->middleware(StaffOnly::class)->group(function () {
        Route::post('/allocate-student', 'allocateStudent'); //allocate student to a tutor
        Route::post('/bulk-allocate', 'bulkAllocate'); //bulk allocation of students to a tutor 
        Route::get('/tutor/{tutor_id}/students', 'getStudentsInfoByTutorId'); //getting assigned student info by tutor id
        Route::get('/student/{student_id}/tutor', 'getTutorInfoByStudentId'); //getting tutor info by assigned student id
        Route::delete('/remove-tutor', 'removeTutorFromStudent');
    });

    // Get, View allocation data for logged in student / logged in tutor
    Route::get('/student/tutor-info', [AllocationController::class, 'getTutorInfoForStudent']); //get tutor info for student
    Route::get('/tutor/students-info', [AllocationController::class, 'getStudentsInfoTutor']); //get student info for student

    // Schedule, Rearrange Meetings
    Route::controller(MeetingController::class)->group(function () {
        Route::post('/meetings/create', 'createMeeting');
        Route::post('/meetings/request', 'requestMeeting')->middleware(UpdateLastActive::class);
        Route::get('/meetings/{meeting_id}', 'getMeetingDetails');
        Route::delete('/meetings/{meeting_id}', 'deleteMeeting');
        Route::patch('/meetings/{id}/update', 'updateMeeting');
        Route::get('/student/meetings', 'getStudentMeetings');
        Route::get('/tutor/meetings', 'getTutorMeetings');

        //for staff
        Route::get('/staff/meetings', 'getAllMeetings'); //get all meeting information
        Route::get('/staff/tutor/{tutorId}/meetings', 'getTutorMeetingsForStaff'); //get tutor meetings for staff
        Route::get('/staff/student/{studentId}/meetings', 'getStudentMeetingsForStaff'); //get student meetings for staff
    });

    // Blogs, and comments
    Route::controller(BlogController::class)->group(function () {
        Route::get('/blogs', 'index');
        Route::get('/blogs/{blog_id}', 'show');
        Route::get('/blogs/user/{user_id}', 'getBlogsByUser');
        Route::post('/blogs', 'store')->middleware(UpdateLastActive::class);
        Route::put('/blogs/{blog_id}', 'update')->middleware(UpdateLastActive::class);
        Route::delete('/blogs/{blog_id}', 'destroy')->middleware(UpdateLastActive::class);
    });

    Route::controller(BlogCommentController::class)->group(function () {
        Route::post('/blogs/{blog_id}/comments', 'store')->middleware(UpdateLastActive::class);
        // Route::post('/blogs/{blog_id}/comments', 'store'); //post comments
        Route::get('/blogs/{blog_id}/comments', 'index');
        Route::put('/blogs/comments/{comment_id}', 'update'); //update comments
        Route::delete('/blogs/comments/{comment_id}', 'destroy')->middleware(UpdateLastActive::class); //delete comments
    });

    //Documents, and comments
    Route::controller(DocumentController::class)->group(function () {
        Route::post('/documents/upload', 'upload')->middleware(UpdateLastActive::class); //upload documents
        Route::post('/documents/{id}/update', 'update')->middleware(UpdateLastActive::class); //update documents
        Route::delete('/documents/{id}/delete', 'delete')->middleware(UpdateLastActive::class); //delete dpciments
        Route::get('/documents', 'index'); //get all documents 
        Route::get('/documents/{user_id}', 'getDocumentsByUserId'); //get all documents 
        Route::get('/documents/tutor-documents', 'viewTutorsDocuments'); //staff only view all tutor documents
        Route::get('/documents/student-documents', 'viewStudentsDocuments'); //staff only view all student documents
        Route::get('/documents/tutor/{tutor_id}/assigned-student-documents', 'getAssignedStudentsDocuments'); //get assigned students' documents by tutor id
    });

    Route::controller(DocumentCommentController::class)->group(function () {
        Route::post('/documents/{id}/comments', 'storeDocumentComment')->middleware(UpdateLastActive::class); //add comments
        Route::get('/documents/{id}/comments', 'getDocumentComments'); //get comments
        Route::put('/documents/comments/{comment_id}', 'updateDocumentComment')->middleware(UpdateLastActive::class); //update comments
        Route::delete('/documents/comments/{comment_id}', 'deleteDocumentComment')->middleware(UpdateLastActive::class); //delete comment
    });

    // Messages
    Route::controller(MessageController::class)->group(function () {
        Route::post('/messages/send', 'sendMessage');
        Route::put('/messages/{message_id}', 'updateMessage');
        Route::delete('/messages/{message_id}', 'deleteMessage');
        Route::get('/messages/users/{user_id}', 'getMessages');
        Route::get('/messages/unread/count', 'getUnreadMessagesCount');
        Route::get('/messages/unread/count/{user_id}', 'getUnreadMessagesCountByUser');
        Route::post('/messages/read/{user_id}', 'markAsRead');
    });


    // Reports
    Route::controller(ReportController::class)->prefix('reports')->group(function(){
        Route::middleware(StaffOnly::class)->group(function(){
            Route::get('/messages/average-per-tutor','getAverageMessagesPerTutor');
            Route::get('/messages/last-7-days','getMessagesLast7Days');
            Route::get('/students/without-tutor','getStudentsWithoutTutor');
            Route::get('/most-used-browsers','getMostUsedBrowsers');
            Route::get('/send-emails/inactive-students',function(){
                Artisan::call('notify:inactive-students');
                return response()->json(['message' => 'Emails sent to inactive students successfully']);
            });
        });
        Route::get('/students/no-interaction/{day}','getStudentsWithNoInteraction');
    });
});


// Authentication
Route::post('/auth/login', [AuthController::class, 'LoginUser']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logoutUser']);

// testing route for browser
// Route::get('/test/',function(Request $request){
//     $agent = new Agent();
//     $user_agent = $agent->browser();
//     $ip_address = $request->ip();
//     return response()->json([
//         'user_agent'=>$user_agent,
//         'ip_address'=>$ip_address
//     ]);
// });