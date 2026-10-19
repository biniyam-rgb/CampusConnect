<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * A student requests enrollment in a course.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isStudent()) {
            return response()->json([
                'message' => 'Only students can enroll in courses.',
            ], 403);
        }

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        $alreadyEnrolled = Enrollment::where('student_id', $user->id)
            ->where('course_id', $validated['course_id'])
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'You are already enrolled in this course.',
            ], 409);
        }

        try {
            $enrollment = Enrollment::create([
                'student_id' => $user->id,
                'course_id' => $validated['course_id'],
                'status' => Enrollment::STATUS_PENDING,
            ]);
        } catch (QueryException $e) {
            // Unique constraint violation (concurrent duplicate request).
            return response()->json([
                'message' => 'You are already enrolled in this course.',
            ], 409);
        }

        return response()->json([
            'message' => 'Enrollment request submitted successfully.',
            'enrollment' => $enrollment->fresh('course'),
        ], 201);
    }

    /**
     * List enrollments scoped to the authenticated user's role.
     *
     * Students see their own enrollments; lecturers see enrollments for the
     * courses they own; admins see all enrollments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $enrollments = $user->enrollments()->with('course')->get();
        } elseif ($user->isLecturer()) {
            $enrollments = Enrollment::whereHas('course', function ($query) use ($user) {
                $query->where('lecturer_id', $user->id);
            })->with(['course', 'student'])->get();
        } else {
            $enrollments = Enrollment::with(['course', 'student'])->get();
        }

        return response()->json([
            'enrollments' => $enrollments,
        ], 200);
    }

    /**
     * Approve a pending enrollment.
     */
    public function approve(Request $request, Enrollment $enrollment): JsonResponse
    {
        return $this->review($request, $enrollment, Enrollment::STATUS_APPROVED);
    }

    /**
     * Reject a pending enrollment.
     */
    public function reject(Request $request, Enrollment $enrollment): JsonResponse
    {
        return $this->review($request, $enrollment, Enrollment::STATUS_REJECTED);
    }

    /**
     * Shared review logic for approve/reject.
     */
    private function review(Request $request, Enrollment $enrollment, string $status): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isLecturer()) {
            return response()->json([
                'message' => 'Only lecturers or admins can review enrollments.',
            ], 403);
        }

        $enrollment->load('course');

        if (! $enrollment->course) {
            return response()->json([
                'message' => 'Enrollment course not found.',
            ], 404);
        }

        // Lecturers can only review enrollments for their own courses.
        if ($user->isLecturer() && (int) $enrollment->course->lecturer_id !== (int) $user->id) {
            return response()->json([
                'message' => 'You can only review enrollments for your own courses.',
            ], 403);
        }

        if (! $enrollment->isPending()) {
            return response()->json([
                'message' => 'Only pending enrollments can be ' . $status . '.',
            ], 422);
        }

        $enrollment->update([
            'status' => $status,
            'reviewed_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Enrollment ' . $status . ' successfully.',
            'enrollment' => $enrollment->fresh(['course', 'reviewer']),
        ], 200);
    }
}
