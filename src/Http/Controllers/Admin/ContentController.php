<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\ContentRevision;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = ContentItem::when(
            config('wlcms.user.model'), 
            fn($q) => $q->with('creator')
        );

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        $content = $query->latest('updated_at')->paginate(20);

        // Calculate stats for filter buttons
        $stats = [
            'total' => ContentItem::count(),
            'published' => ContentItem::where('status', 'published')->count(),
            'draft' => ContentItem::where('status', 'draft')->count(),
            'archived' => ContentItem::where('status', 'archived')->count(),
        ];

        return view('wlcms::admin.content.index', compact('content', 'stats'));
    }

    public function create()
    {
        return view('wlcms::admin.content.create');
    }

    public function show(ContentItem $content)
    {
        return view('wlcms::admin.content.show', compact('content'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_content_items,slug',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'type' => 'required|string|in:page,post,article,news,event',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'meta' => 'nullable|array',
            'show_in_menu' => 'boolean',
            'menu_title' => 'nullable|string|max:255',
            'menu_order' => 'integer|min:0',
            'menu_location' => 'string|in:primary,footer,sidebar',
            'featured_media_id' => 'nullable|exists:cms_media_assets,id',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:cms_media_assets,id',
        ]);

        // Convert checkbox value properly
        $validated['show_in_menu'] = $request->has('show_in_menu');
        $validated['menu_order'] = $validated['menu_order'] ?? 0;
        $validated['menu_location'] = $validated['menu_location'] ?? 'primary';

        $content = ContentItem::create($validated);

        // Attach featured media
        if ($request->filled('featured_media_id')) {
            $content->mediaAssets()->attach($request->featured_media_id, [
                'type' => 'featured',
                'sort_order' => 0
            ]);
        }

        // Attach additional media (gallery, attachments, etc.)
        if ($request->filled('media_ids')) {
            foreach ($request->media_ids as $index => $mediaId) {
                $content->mediaAssets()->attach($mediaId, [
                    'type' => 'attachment',
                    'sort_order' => $index + 1
                ]);
            }
        }

        return redirect()->route('wlcms.admin.content.index')
                        ->with('success', 'Content created successfully!');
    }

    public function edit(ContentItem $content)
    {
        $content->load('mediaAssets');
        return view('wlcms::admin.content.edit', compact('content'));
    }

    public function update(Request $request, ContentItem $content)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_content_items,slug,' . $content->id,
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'type' => 'required|string|in:page,post,article,news,event',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'meta' => 'nullable|array',
            'show_in_menu' => 'boolean',
            'menu_title' => 'nullable|string|max:255',
            'menu_order' => 'integer|min:0',
            'featured_media_id' => 'nullable|exists:cms_media_assets,id',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:cms_media_assets,id',
        ]);

        // Convert checkbox value properly
        $validated['show_in_menu'] = $request->has('show_in_menu');
        $validated['menu_order'] = $validated['menu_order'] ?? 0;
        $validated['menu_location'] = $validated['menu_location'] ?? 'primary';

        $content->update($validated);

        // Sync featured media
        $content->mediaAssets()->wherePivot('type', 'featured')->detach();
        if ($request->filled('featured_media_id')) {
            $content->mediaAssets()->attach($request->featured_media_id, [
                'type' => 'featured',
                'sort_order' => 0
            ]);
        }

        // Sync additional media
        $content->mediaAssets()->wherePivot('type', 'attachment')->detach();
        if ($request->filled('media_ids')) {
            foreach ($request->media_ids as $index => $mediaId) {
                $content->mediaAssets()->attach($mediaId, [
                    'type' => 'attachment',
                    'sort_order' => $index + 1
                ]);
            }
        }
        $content->update($validated);

        return redirect()->route('wlcms.admin.content.index')
                        ->with('success', 'Content updated successfully!');
    }

    public function destroy(ContentItem $content)
    {
        $content->delete();

        return redirect()->route('wlcms.admin.content.index')
                        ->with('success', 'Content deleted successfully!');
    }

    public function publish(ContentItem $content)
    {
        $content->update([
            'status' => 'published',
            'published_at' => now()
        ]);

        return redirect()->back()
                        ->with('success', 'Content published successfully!');
    }

    public function unpublish(ContentItem $content)
    {
        $content->update([
            'status' => 'draft',
            'published_at' => null
        ]);

        return redirect()->back()
                        ->with('success', 'Content moved to draft!');
    }

    public function preview(ContentItem $content)
    {
        return view('wlcms::admin.content.preview', compact('content'));
    }

    public function revisions(ContentItem $content)
    {
        $revisions = $content->revisions()->latest()->get();

        return view('wlcms::admin.content.revisions', compact('content', 'revisions'));
    }

    /**
     * Attach media to content item
     */
    public function attachMedia(Request $request, ContentItem $content)
    {
        $validated = $request->validate([
            'media_id' => 'required|exists:cms_media_assets,id',
            'type' => 'required|string|in:featured,gallery,attachment',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // If featured, remove existing featured media
        if ($validated['type'] === 'featured') {
            $content->mediaAssets()->wherePivot('type', 'featured')->detach();
        }

        $content->mediaAssets()->attach($validated['media_id'], [
            'type' => $validated['type'],
            'sort_order' => $validated['sort_order'] ?? 0
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Media attached successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Media attached successfully');
    }

    /**
     * Detach media from content item
     */
    public function detachMedia(Request $request, ContentItem $content)
    {
        $validated = $request->validate([
            'media_id' => 'required|exists:cms_media_assets,id',
        ]);

        $content->mediaAssets()->detach($validated['media_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Media detached successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Media removed successfully');
    }
}