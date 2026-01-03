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
        
        return view('wlcms::admin.content.index', compact('content'));
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
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'type' => 'required|string|in:page,post,article,news,event',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'meta' => 'nullable|array',
        ]);

        $content = ContentItem::create($validated);

        return redirect()->route('wlcms.admin.content.index')
                        ->with('success', 'Content created successfully!');
    }

    public function edit(ContentItem $content)
    {
        return view('wlcms::admin.content.edit', compact('content'));
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
}