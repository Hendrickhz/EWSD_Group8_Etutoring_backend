<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Middleware\StaffOnly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    // Allocation, Reallocation
    Route::controller(AllocationController::class)->middleware(StaffOnly::class)->group(function () {
        Route::post('/allocate-student', 'allocateStudent');
        Route::post('/bulk-allocate', 'bulkAllocate');
        Route::get('/tutor/{id}/students', 'getTutorStudents');
        Route::delete('/remove-tutor', 'removeTutorFromStudent');
    });

    // Schedule, Rearrange Meetings
    Route::controller(MeetingController::class)->group(function () {
        Route::post('/meetings/create', 'createMeeting');
        Route::post('/meetings/request', 'requestMeeting');
        Route::get('/meetings/{meeting_id}', 'getMeetingDetails');
        Route::patch('/meetings/{id}/update', 'updateMeeting');
        Route::get('/student/meetings', 'getStudentMeetings');
        Route::get('/tutor/meetings', 'getTutorMeetings');

        //for staff
        Route::get('/staff/meetings', 'getAllMeetings');
        Route::get('/staff/tutor/{tutorId}/meetings', 'getTutorMeetingsForStaff');
        Route::get('/staff/student/{studentId}/meetings', 'getStudentMeetingsForStaff');
    });
});

// Authentication
Route::post('/auth/login', [AuthController::class, 'LoginUser']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logoutUser']);