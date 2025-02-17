<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
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