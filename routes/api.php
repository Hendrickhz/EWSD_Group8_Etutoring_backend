<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\BlogController;
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

    // Blogs, and comments
    Route::controller(BlogController::class)->group(function () {
        Route::get('/blogs', 'index');
        Route::get('/blogs/{blog_id}', 'show');
        Route::get('/blogs/user/{user_id}', 'getBlogsByUser');
        Route::post('/blogs', 'store');
        Route::put('/blogs/{blog_id}', 'update');
        Route::delete('/blogs/{blog_id}', 'destroy');
    });
    Route::controller(BlogCommentController::class)->group(function () {
        Route::post('/blogs/{blog_id}/comments', 'store');
        Route::get('/blogs/{blog_id}/comments', 'index');
        Route::put('/comments/{comment_id}', 'update');
        Route::delete('/comments/{comment_id}', 'destroy');
    });
});

});

// Authentication
Route::post('/auth/login', [AuthController::class, 'LoginUser']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logoutUser']);

