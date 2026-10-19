<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * List all courses.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with('lecturer:id,name,email')->get();

        return response()->json($courses);
    }

    /**
     * Create a new course.
     *
     * Lecturers can only create courses assigned to themselves; any
     * `lecturer_id` they submit is ignored. Admins may assign a course to
     * any lecturer.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'course_code' => ['required', 'string', 'max:255', 'unique:courses,course_code'],
            'description' => ['nullable', 'string'],
        ];

        if ($user->isAdmin()) {
            $rules['lecturer_id'] = ['required', 'exists:users,id,role,lecturer'];
        }
        // For lecturers, lecturer_id is intentionally NOT validated: it is
        // ignored from input and forced to the lecturer's own id below.

        $validated = $request->validate($rules);

        if ($user->isLecturer()) {
            $validated['lecturer_id'] = $user->id;
        }

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course->fresh('lecturer'),
        ], 201);
    }

    /**
     * Show a single course.
     */
    public function show(string $id): JsonResponse
    {
        $course = Course::with('lecturer:id,name,email')->find($id);

        if (! $course) {
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        }

        return response()->json($course);
    }

    /**
     * Update an existing course.
     *
     * Lecturers may update only their own courses and cannot reassign
     * ownership. Admins may update any course, including reassigning the
     * lecturer.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $course = Course::find($id);

        if (! $course) {
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        }

        $user = $request->user();

        if (! $user->isAdmin() && (int) $course->lecturer_id !== (int) $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this course.',
            ], 403);
        }

        $rules = [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'course_code' => ['sometimes', 'required', 'string', 'max:255', 'unique:courses,course_code,' . $course->id],
            'description' => ['sometimes', 'nullable', 'string'],
        ];

        // Only admins may reassign the course to a different lecturer.
        if ($user->isAdmin()) {
            $rules['lecturer_id'] = ['sometimes', 'required', 'exists:users,id,role,lecturer'];
        }

        $validated = $request->validate($rules);

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course->fresh('lecturer'),
        ]);
    }

    /**
     * Delete an existing course.
     *
     * Only admins or the lecturer who owns the course may delete it.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $course = Course::find($id);

        if (! $course) {
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        }

        $user = $request->user();

        if (! $user->isAdmin() && (int) $course->lecturer_id !== (int) $user->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this course.',
            ], 403);
        }

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }
}
