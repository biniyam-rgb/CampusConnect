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
}
