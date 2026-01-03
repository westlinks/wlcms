<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\ContentRevision;

class ContentController extends Controller
{
    public function index()
    {
        $content = ContentItem::with('revisions')->latest()->paginate(15);
        
        return response()->json([
            'content' => $content,
            'message' => 'Content list retrieved successfully'
        ]);
    }

    public function show(ContentItem $content)
    {
        return response()->json([
            'content' => $content->load(['revisions', 'mediaAssets']),
            'message' => 'Content retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'type' => 'required|string|in:page,post,article,news,event',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'meta' => 'nullable|array',
        ]);

        $content = ContentItem::create($validated);

        return response()->json([
            'content' => $content,
            'message' => 'Content created successfully'
        ], 201);
    }

    public function update(Request $request, ContentItem $content)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'type' => 'required|string|in:page,post,article,news,event',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'meta' => 'nullable|array',
        ]);

        $content->update($validated);

        return response()->json([
            'content' => $content,
            'message' => 'Content updated successfully'
        ]);
    }

    public function destroy(ContentItem $content)
    {
        $content->delete();

        return response()->json([
            'message' => 'Content deleted successfully'
        ]);
    }

    public function publish(ContentItem $content)
    {
        $content->update([
            'status' => 'published',
            'published_at' => now()
        ]);

        return response()->json([
            'content' => $content,
            'message' => 'Content published successfully'
        ]);
    }

    public function unpublish(ContentItem $content)
    {
        $content->update([
            'status' => 'draft',
            'published_at' => null
        ]);

        return response()->json([
            'content' => $content,
            'message' => 'Content unpublished successfully'
        ]);
    }

    public function preview(ContentItem $content)
    {
        return response()->json([
            'content' => $content->load('mediaAssets'),
            'message' => 'Content preview retrieved successfully'
        ]);
    }

    public function revisions(ContentItem $content)
    {
        $revisions = $content->revisions()->latest()->get();

        return response()->json([
            'revisions' => $revisions,
            'message' => 'Content revisions retrieved successfully'
        ]);
    }
}