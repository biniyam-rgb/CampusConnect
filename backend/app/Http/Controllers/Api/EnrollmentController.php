
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    // Students request enrollment in a course
    public function store(Request $request)
    {
        if ($request->user()->role !== 'student') {
            return response()->json([
                'message' => 'Only students can enroll in courses.',
            ], 403);
        }

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

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

    // Students view their own enrollments
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

    // Lecturers approve enrollment requests for their own courses
    public function approve(Request $request, Enrollment $enrollment)
    {
        if ($request->user()->role !== 'lecturer') {
            return response()->json([
                'message' => 'Only lecturers can approve enrollments.',
            ], 403);
        }

        if ($enrollment->course->lecturer_id != $request->user()->id) {
            return response()->json([
                'message' => 'You can only review enrollments for your own courses.',
            ], 403);
        }

        if ($enrollment->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending enrollments can be approved.',
            ], 422);
        }

        $enrollment->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Enrollment approved successfully.',
            'enrollment' => $enrollment->fresh(),
        ], 200);
    }

    // Lecturers reject enrollment requests for their own courses
    public function reject(Request $request, Enrollment $enrollment)
    {
        if ($request->user()->role !== 'lecturer') {
            return response()->json([
                'message' => 'Only lecturers can reject enrollments.',
            ], 403);
        }

        if ($enrollment->course->lecturer_id != $request->user()->id) {
            return response()->json([
                'message' => 'You can only review enrollments for your own courses.',
            ], 403);
        }

        if ($enrollment->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending enrollments can be rejected.',
            ], 422);
        }

        $enrollment->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Enrollment rejected successfully.',
            'enrollment' => $enrollment->fresh(),
        ], 200);
    }
}
