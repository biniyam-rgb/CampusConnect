<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    /**
     * List resources for a course.
     *
     * Students must be approved in the course; the course lecturer and admins
     * may always view resources.
     */
    public function index(Request $request, Course $course): JsonResponse
    {
        $denied = $this->authorizeCourseAccess($request, $course);
        if ($denied) {
            return $denied;
        }

        $resources = $course->resources()->with('uploader:id,name,email')->get();

        return response()->json([
            'resources' => $resources,
        ], 200);
    }

    /**
     * Upload a resource for a course.
     *
     * Only the course lecturer (or an admin) can upload resources. Files are
     * stored on the private `local` disk and never exposed by URL.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && (int) $course->lecturer_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Only the course lecturer can upload resources.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,txt', 'max:10240'],
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

    /**
     * Download a course resource.
     */
    public function download(Request $request, Resource $resource): JsonResponse|StreamedResponse
    {
        $course = $resource->course;

        $denied = $this->authorizeCourseAccess($request, $course);
        if ($denied) {
            return $denied;
        }

        if (! Storage::disk('local')->exists($resource->file_path)) {
            return response()->json([
                'message' => 'Resource file not found.',
            ], 404);
        }

        return Storage::disk('local')->download($resource->file_path);
    }

    /**
     * Delete a course resource.
     *
     * Only the course lecturer (or an admin) can delete a resource.
     */
    public function destroy(Request $request, Resource $resource): JsonResponse
    {
        $user = $request->user();
        $course = $resource->course;

        if (! $user->isAdmin() && (int) $course->lecturer_id !== (int) $user->id) {
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

    /**
     * Ensure the authenticated user may access the given course's resources.
     *
     * @return JsonResponse|null Returns a 403 JsonResponse when access is
     *                           denied, or null when access is allowed.
     */
    private function authorizeCourseAccess(Request $request, Course $course): ?JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return null;
        }

        if ($user->isLecturer()) {
            if ((int) $course->lecturer_id !== (int) $user->id) {
                return response()->json([
                    'message' => 'You cannot view resources for this course.',
                ], 403);
            }

            return null;
        }

        // Students must have an approved enrollment.
        $isApproved = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('status', Enrollment::STATUS_APPROVED)
            ->exists();

        if (! $isApproved) {
            return response()->json([
                'message' => 'You must be approved in this course to access its resources.',
            ], 403);
        }

        return null;
    }
}
