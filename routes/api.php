<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
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
        Route::get('/get-all-students', 'getAllStudents');
        Route::get('/get-all-tutors', 'getAllTutors');
    });

    // Allocation, Reallocation Managed by Staff only
    Route::controller(AllocationController::class)->middleware(StaffOnly::class)->group(function () {
        Route::post('/allocate-student', 'allocateStudent');
        Route::post('/bulk-allocate', 'bulkAllocate');
        Route::get('/tutor/{tutor_id}/students', 'getStudentsInfoByTutorId');
        Route::get('/student/{student_id}/tutor', 'getTutorInfoByStudentId');
        Route::delete('/remove-tutor', 'removeTutorFromStudent');
    });

    // Get, View allocation data for logged in student / logged in tutor
    Route::get('/student/tutor-info', [AllocationController::class, 'getTutorInfoForStudent']);
    Route::get('/tutor/students-info', [AllocationController::class, 'getStudentsInfoTutor']);

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