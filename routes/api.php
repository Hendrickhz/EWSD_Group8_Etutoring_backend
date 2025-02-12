<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Allocation, Reallocation
Route::controller(AllocationController::class)->group(function () {
    Route::post('/allocate-student', 'allocateStudent');
    Route::post('/bulk-allocate', 'bulkAllocate');
    Route::get('/tutor/{id}/students', 'getTutorStudents');
    Route::delete('/remove-tutor', 'removeTutorFromStudent');
});


Route::post('/auth/login',[AuthController::class, 'LoginUser']);

Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logoutUser']);