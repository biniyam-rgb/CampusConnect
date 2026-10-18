<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    // List resources for a course
    public function index(Course $course)
    {
        $user = request()->user();

        if ($user->role === 'student') {
            $isEnrolled = $user->enrollments()
                ->where('course_id', $course->id)
                ->where('status', 'approved')
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'message' => 'You must be approved in this course to view its resources.',
                ], 403);
            }
        } elseif (
            $user->role !== 'admin' &&
            $course->lecturer_id !== $user->id
        ) {
            return response()->json([
                'message' => 'You cannot view resources for this course.',
            ], 403);
        }

        $resources = $course->resources()->with('uploader')->get();

        return response()->json([
            'resources' => $resources,
        ], 200);
    }

    // Upload a resource for a course
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        if (
            $user->role !== 'lecturer' ||
            $course->lecturer_id !== $user->id
        ) {
            return response()->json([
                'message' => 'Only the course lecturer can upload resources.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,txt',
                'max:10240',
            ],
        ]);

        $path = $request->file('file')->store('resources', 'local');

        $resource = Resource::create([
            'course_id' => $course->id,
            'uploaded_by' => $user->id,
            'title' => $validated['title'],
            'file_path' => $path,
        ]);

        return response()->json([
            'message' => 'Resource uploaded successfully.',
            'resource' => $resource,
        ], 201);
    }

    // Delete a resource
    public function destroy(Request $request, Resource $resource)
    {
        $user = $request->user();

        $course = $resource->course;

        if (
            $user->role !== 'admin' &&
            !(
                $user->role === 'lecturer' &&
                $course->lecturer_id === $user->id
            )
        ) {
            return response()->json([
                'message' => 'You cannot delete this resource.',
            ], 403);
        }

        Storage::disk('local')->delete($resource->file_path);

        $resource->delete();

        return response()->json([
            'message' => 'Resource deleted successfully.',
        ], 200);
    }
}
