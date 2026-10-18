<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ResourceController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/enrollments', [
    EnrollmentController::class,
    'store'
])->middleware('auth:sanctum');

Route::get('/admin-test', function () {
    return response()->json([
        'message' => 'Welcome, Admin! Authorization successful.',
    ]);
})->middleware(['auth:sanctum', 'role:admin']);


Route::get('/lecturer-test', function () {
    return response()->json([
        'message' => 'Welcome, Lecturer! Authorization successful.',
    ]);
})->middleware(['auth:sanctum', 'role:lecturer']);

Route::get('/student-test', function () {
    return response()->json([
        'message' => 'Welcome, Student! Authorization successful.',
    ]);
})->middleware(['auth:sanctum', 'role:student']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/courses', [CourseController::class, 'index']);

    Route::post('/courses', [CourseController::class, 'store'])
        ->middleware('role:admin,lecturer');
});

Route::get('/courses/{id}', [CourseController::class, 'show']);

Route::put('/courses/{id}', [CourseController::class, 'update'])
    ->middleware('role:admin,lecturer');

    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])
    ->middleware('role:admin,lecturer');

    Route::post('/enrollments', [
    EnrollmentController::class,
    'store'
])->middleware('auth:sanctum');

Route::get('/enrollments', [
    EnrollmentController::class,
    'index'
])->middleware('auth:sanctum');

Route::patch('/enrollments/{enrollment}/approve', [
    EnrollmentController::class,
    'approve'
])->middleware('auth:sanctum');

Route::patch('/enrollments/{enrollment}/reject', [
    EnrollmentController::class,
    'reject'
])->middleware('auth:sanctum');

Route::get('/courses/{course}/resources', [
    ResourceController::class,
    'index'
])->middleware('auth:sanctum');

Route::post('/courses/{course}/resources', [
    ResourceController::class,
    'store'
])->middleware('auth:sanctum');

Route::delete('/resources/{resource}', [
    ResourceController::class,
    'destroy'
])->middleware('auth:sanctum');

Route::get('/resources/{resource}/download', [
    ResourceController::class,
    'download'
])->middleware('auth:sanctum');