<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function store(Request $request)
    {
        // Only students can enroll
        if ($request->user()->role !== 'student') {
            return response()->json([
                'message' => 'Only students can enroll in courses.',
            ], 403);
        }

        // Validate the course ID
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        // Check for an existing enrollment
        $existingEnrollment = Enrollment::where(
            'student_id',
            $request->user()->id
        )->where(
            'course_id',
            $validated['course_id']
        )->exists();

        if ($existingEnrollment) {
            return response()->json([
                'message' => 'You are already enrolled in this course.',
            ], 409);
        }

        // Create the enrollment
        $enrollment = Enrollment::create([
            'student_id' => $request->user()->id,
            'course_id' => $validated['course_id'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Enrollment request submitted successfully.',
            'enrollment' => $enrollment,
        ], 201);
    }

    public function index(Request $request)
{
    if ($request->user()->role !== 'student') {
        return response()->json([
            'message' => 'Only students can view their enrollments.',
        ], 403);
    }

    $enrollments = $request->user()
        ->enrollments()
        ->with('course')
        ->get();

    return response()->json([
        'enrollments' => $enrollments,
    ], 200);
}
}
