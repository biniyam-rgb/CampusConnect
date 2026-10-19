<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List published announcements.
     *
     * Available to any authenticated user. An announcement is considered
     * published when it has no publish date or its publish date is in the
     * past. Admins additionally see scheduled/announced entries (i.e. all).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with('admin:id,name,email')->latest();

        if (! $request->user()->isAdmin()) {
            $query->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
        }

        return response()->json([
            'announcements' => $query->get(),
        ], 200);
    }

    /**
     * Show a single announcement.
     */
    public function show(Request $request, Announcement $announcement): JsonResponse
    {
        $isPublished = $announcement->published_at === null
            || $announcement->published_at->lessThanOrEqualTo(now());

        if (! $isPublished && ! $request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Announcement not found.',
            ], 404);
        }

        $announcement->load('admin:id,name,email');

        return response()->json([
            'announcement' => $announcement,
        ], 200);
    }

    /**
     * Create a new announcement (admin only).
     *
     * The admin_id is always set from the authenticated user, never from
     * request input, to prevent spoofing the author.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ]);

        $announcement = Announcement::create([
            'admin_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Announcement created successfully.',
            'announcement' => $announcement->fresh('admin:id,name,email'),
        ], 201);
    }

    /**
     * Update an existing announcement (admin only).
     */
    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $announcement->update($validated);

        return response()->json([
            'message' => 'Announcement updated successfully.',
            'announcement' => $announcement->fresh('admin:id,name,email'),
        ]);
    }

    /**
     * Delete an announcement (admin only).
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully.',
        ]);
    }
}
