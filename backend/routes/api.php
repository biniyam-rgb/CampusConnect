<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ResourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public authentication routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated API routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Courses
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::post('/courses', [CourseController::class, 'store'])
        ->middleware('role:admin,lecturer');
    Route::put('/courses/{id}', [CourseController::class, 'update'])
        ->middleware('role:admin,lecturer');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])
        ->middleware('role:admin,lecturer');

    // Course resources (files are private; access enforced in the controller)
    Route::get('/courses/{course}/resources', [ResourceController::class, 'index']);
    Route::post('/courses/{course}/resources', [ResourceController::class, 'store']);
    Route::get('/resources/{resource}/download', [ResourceController::class, 'download']);
    Route::delete('/resources/{resource}', [ResourceController::class, 'destroy']);

    // Enrollments
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::patch('/enrollments/{enrollment}/approve', [EnrollmentController::class, 'approve']);
    Route::patch('/enrollments/{enrollment}/reject', [EnrollmentController::class, 'reject']);

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);
    Route::post('/announcements', [AnnouncementController::class, 'store'])
        ->middleware('role:admin');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])
        ->middleware('role:admin');
    Route::patch('/announcements/{announcement}', [AnnouncementController::class, 'update'])
        ->middleware('role:admin');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
        ->middleware('role:admin');

    // Role smoke-test endpoints
    Route::get('/admin-test', fn () => response()->json([
        'message' => 'Welcome, Admin! Authorization successful.',
    ]))->middleware('role:admin');

    Route::get('/lecturer-test', fn () => response()->json([
        'message' => 'Welcome, Lecturer! Authorization successful.',
    ]))->middleware('role:lecturer');

    Route::get('/student-test', fn () => response()->json([
        'message' => 'Welcome, Student! Authorization successful.',
    ]))->middleware('role:student');
});
