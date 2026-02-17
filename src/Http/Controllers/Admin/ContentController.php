<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\ContentRevision;
use Westlinks\Wlcms\Models\Template;

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
            'template_identifier' => 'nullable|string|exists:cms_templates,identifier',
            'meta' => 'nullable|array',
            'show_in_menu' => 'boolean',
            'menu_title' => 'nullable|string|max:255',
            'menu_order' => 'integer|min:0',
            'menu_location' => 'string|in:primary,footer,sidebar',
            'featured_media_id' => 'nullable|exists:cms_media_assets,id',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:cms_media_assets,id',
            'zones_json' => 'nullable|string',
            'zones' => 'nullable|array',
            'settings_json' => 'nullable|string',
            'auto_activate' => 'boolean',
            'auto_deactivate' => 'boolean',
            'activation_date' => 'nullable|date',
            'deactivation_date' => 'nullable|date|after:activation_date',
        ]);

        // Convert checkbox values properly
        $validated['show_in_menu'] = $request->has('show_in_menu');
        $validated['auto_activate'] = $request->has('auto_activate');
        $validated['auto_deactivate'] = $request->has('auto_deactivate');
        $validated['menu_order'] = $validated['menu_order'] ?? 0;
        $validated['menu_location'] = $validated['menu_location'] ?? 'primary';

        // Map template_identifier to template column
        if (isset($validated['template_identifier'])) {
            $validated['template'] = $validated['template_identifier'];
            unset($validated['template_identifier']);
        }

        // Validate required zones if template has been selected
        if ($request->filled('template_identifier')) {
            $template = Template::where('identifier', $request->template_identifier)->first();
            if ($template) {
                $zonesData = $request->zones_json ? json_decode($request->zones_json, true) : ($request->zones ?? []);
                $this->validateRequiredZones($template, $zonesData);
            }
        }

        $content = ContentItem::create($validated);

        // Extract featured_image from template settings if provided
        $settingsData = $request->settings_json ? json_decode($request->settings_json, true) : [];
        $featuredMediaId = $settingsData['featured_image'] ?? $request->featured_media_id;
        
        \Log::info('STORE - Settings Data:', ['settings' => $settingsData]);
        \Log::info('STORE - Featured Media ID:', ['id' => $featuredMediaId, 'from_settings' => $settingsData['featured_image'] ?? null, 'from_request' => $request->featured_media_id]);

        // Attach featured media
        if ($featuredMediaId) {
            $content->mediaAssets()->attach($featuredMediaId, [
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

        // Save template zones data and settings
        if ($request->filled('template_identifier') && ($request->filled('zones_json') || $request->filled('zones'))) {
            $zonesData = $request->zones_json ? json_decode($request->zones_json, true) : $request->zones;
            
            $content->templateSettings()->updateOrCreate(
                ['content_id' => $content->id],
                [
                    'settings' => $settingsData ?? [],
                    'zones_data' => $zonesData ?? []
                ]
            );
        }

        return redirect()->route('wlcms.admin.content.show', $content)
                        ->with('success', 'Content created successfully!');
    }

    public function edit(ContentItem $content)
    {
        $content->load('mediaAssets', 'templateSettings');
        
        $featuredMedia = $content->mediaAssets->first(function($media) {
            return $media->pivot->type === 'featured';
        });
        \Log::info('EDIT - Loading content:', [
            'content_id' => $content->id,
            'featured_media_id' => $featuredMedia?->id,
            'template_settings' => $content->templateSettings?->settings,
        ]);
        
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
            'template_identifier' => 'nullable|string|exists:cms_templates,identifier',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'meta' => 'nullable|array',
            'show_in_menu' => 'boolean',
            'menu_title' => 'nullable|string|max:255',
            'menu_order' => 'integer|min:0',
            'featured_media_id' => 'nullable|exists:cms_media_assets,id',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:cms_media_assets,id',
            'zones_json' => 'nullable|string',
            'zones' => 'nullable|array',
            'settings_json' => 'nullable|string',
            'auto_activate' => 'boolean',
            'auto_deactivate' => 'boolean',
            'activation_date' => 'nullable|date',
            'deactivation_date' => 'nullable|date|after:activation_date',
        ]);

        // Convert checkbox values properly
        $validated['show_in_menu'] = $request->has('show_in_menu');
        $validated['auto_activate'] = $request->has('auto_activate');
        $validated['auto_deactivate'] = $request->has('auto_deactivate');
        $validated['menu_order'] = $validated['menu_order'] ?? 0;
        $validated['menu_location'] = $validated['menu_location'] ?? 'primary';

        // Map template_identifier to template column
        if (isset($validated['template_identifier'])) {
            $validated['template'] = $validated['template_identifier'];
            unset($validated['template_identifier']);
        }

        // Validate required zones if template has been selected
        if ($request->filled('template_identifier')) {
            $template = Template::where('identifier', $request->template_identifier)->first();
            if ($template) {
                $zonesData = $request->zones_json ? json_decode($request->zones_json, true) : ($request->zones ?? []);
                $this->validateRequiredZones($template, $zonesData);
            }
        }

        $content->update($validated);

        // Extract featured_image from template settings if provided
        $settingsData = $request->settings_json ? json_decode($request->settings_json, true) : [];
        $featuredMediaId = $settingsData['featured_image'] ?? $request->featured_media_id;
        
        \Log::info('UPDATE - Settings Data:', ['settings' => $settingsData]);
        \Log::info('UPDATE - Featured Media ID:', ['id' => $featuredMediaId, 'from_settings' => $settingsData['featured_image'] ?? null, 'from_request' => $request->featured_media_id]);

        // Sync featured media
        $content->mediaAssets()->wherePivot('type', 'featured')->detach();
        if ($featuredMediaId) {
            $content->mediaAssets()->attach($featuredMediaId, [
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

        // Update template zones data and settings
        if ($request->filled('template_identifier') && ($request->filled('zones_json') || $request->filled('zones'))) {
            $zonesData = $request->zones_json ? json_decode($request->zones_json, true) : $request->zones;
            
            $content->templateSettings()->updateOrCreate(
                ['content_id' => $content->id],
                [
                    'settings' => $settingsData ?? [],
                    'zones_data' => $zonesData ?? []
                ]
            );
        }

        return redirect()->route('wlcms.admin.content.show', $content)
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

    /**
     * Validate that all required zones have content
     */
    protected function validateRequiredZones(Template $template, array $zonesData): void
    {
        $templateZones = $template->zones ?? [];
        $missingZones = [];

        foreach ($templateZones as $zoneKey => $zoneConfig) {
            if (isset($zoneConfig['required']) && $zoneConfig['required']) {
                if (empty($zonesData[$zoneKey])) {
                    $zoneLabel = $zoneConfig['label'] ?? $zoneKey;
                    $missingZones[] = $zoneLabel;
                }
            }
        }

        if (!empty($missingZones)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'zones_json' => 'The following required zones must be filled: ' . implode(', ', $missingZones)
            ]);
        }
    }
}