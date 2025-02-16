<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\StaffController;
use App\Http\Middleware\StaffOnly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {

    // For Staff
    Route::controller(StaffController::class)->middleware(StaffOnly::class)->prefix('staff')->group(function(){
        Route::get('/get-all-students','getAllStudents');
        Route::get('/get-all-tutors','getAllTutors');
    });

    // Allocation, Reallocation
    Route::controller(AllocationController::class)->middleware(StaffOnly::class)->group(function () {
        Route::post('/allocate-student', 'allocateStudent');
        Route::post('/bulk-allocate', 'bulkAllocate');
        Route::get('/tutor/{id}/students', 'getTutorStudents');
        Route::get('/student/tutor-info', 'getTutorInfoForStudent')->withoutMiddleware(StaffOnly::class);
        Route::delete('/remove-tutor', 'removeTutorFromStudent');
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
});

// Authentication
Route::post('/auth/login', [AuthController::class, 'LoginUser']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logoutUser']);