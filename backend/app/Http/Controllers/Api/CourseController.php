<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // List all courses
    public function index()
    {
        return response()->json(
            Course::with('lecturer:id,name,email')->get()
        );
    }

    // Create a new course
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:255|unique:courses,course_code',
            'description' => 'nullable|string',
            'lecturer_id' => 'required|exists:users,id,role,lecturer',
        ]);

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course,
        ], 201);
    }

    
    // Show one course
    public function show(string $id)
    {
        $course = Course::with('lecturer:id,name,email')->find($id);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        }

        return response()->json($course);
    }

    
    // Update an existing course
    public function update(Request $request, string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        }

        $user = $request->user();

        if ($user->role !== 'admin' && $course->lecturer_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this course.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'course_code' => 'sometimes|required|string|max:255|unique:courses,course_code,' . $course->id,
            'description' => 'sometimes|nullable|string',
            'lecturer_id' => 'sometimes|required|exists:users,id,role,lecturer',
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course->fresh(),
        ]);
    }


    // Delete an existing course
    public function destroy(Request $request, string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        }

        $user = $request->user();

        if (
            $user->role !== 'admin' &&
            (int) $course->lecturer_id !== (int) $user->id
        ) {
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
