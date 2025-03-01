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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
        Route::post('/meetings/create', 'createMeeting'); //create meetings by tutor
        Route::post('/meetings/request', 'requestMeeting'); //request meetings by student
        Route::get('/meetings/{meeting_id}', 'getMeetingDetails'); //view meeting details
        Route::delete('/meetings/{meeting_id}', 'deleteMeeting'); //delete meetings
        Route::patch('/meetings/{id}/update', 'updateMeeting'); //update meetings
        Route::get('/student/meetings', 'getStudentMeetings'); //view student meetings
        Route::get('/tutor/meetings', 'getTutorMeetings'); //view tutor meetings

        //for staff
        Route::get('/staff/meetings', 'getAllMeetings'); //get all meeting information
        Route::get('/staff/tutor/{tutorId}/meetings', 'getTutorMeetingsForStaff'); //get tutor meetings for staff
        Route::get('/staff/student/{studentId}/meetings', 'getStudentMeetingsForStaff'); //get student meetings for staff
    });

    // Blogs, and comments
    Route::controller(BlogController::class)->group(function () {
        Route::get('/blogs', 'index');
        Route::get('/blogs/{blog_id}', 'show'); //view blogs
        Route::get('/blogs/user/{user_id}', 'getBlogsByUser'); //view blogs by user
        Route::post('/blogs', 'store'); //create blogs
        Route::put('/blogs/{blog_id}', 'update'); //update blogs
        Route::delete('/blogs/{blog_id}', 'destroy'); //delete blogs
    });

    Route::controller(BlogCommentController::class)->group(function () {
        Route::post('/blogs/{blog_id}/comments', 'store'); //post comments
        Route::get('/blogs/{blog_id}/comments', 'index');
        Route::put('/comments/{comment_id}', 'update'); //update comments
        Route::delete('/comments/{comment_id}', 'destroy'); //delete comments
    });

    //Documents, and comments
    Route::controller(DocumentController::class)->group(function () {
        Route::post('/documents/upload', 'upload'); //upload documents
        Route::post('/documents/{id}/update', 'update'); //update documents
        Route::delete('/documents/{id}/delete', 'delete'); //delete dpciments
        Route::get('/documents', 'index'); //get all documents 
        Route::get('/documents/tutor-documents', 'viewTutorsDocuments'); //staff only view all tutor documents
        Route::get('/documents/student-documents', 'viewStudentsDocuments'); //staff only view all student documents
        Route::get('/documents/tutor/{tutor_id}/assigned-student-documents', 'getAssignedStudentsDocuments'); //get assigned students' documents by tutor id
    });

    Route::controller(DocumentCommentController::class)->group(function () {
        Route::post('/documents/{id}/comments', 'storeDocumentComment'); //add comments
        Route::get('/documents/{id}/comments', 'getDocumentComments'); //get comments
        Route::put('/comments/{comment_id}', 'updateDocumentComment'); //update comments
        Route::delete('/comments/{comment_id}', 'deleteDocumentComment'); //delete comment
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
            Route::get('/students/no-interaction/{day}','getStudentsWithNoInteraction');
        });
    });
});


// Authentication
Route::post('/auth/login', [AuthController::class, 'LoginUser']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logoutUser']);
