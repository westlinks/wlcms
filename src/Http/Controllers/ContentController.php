<?php

namespace Westlinks\Wlcms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\ContentItem;

class ContentController extends Controller
{
    public function show(Request $request, $parent = null, $content = null)
    {
        // Handle both single-level and hierarchical content URLs
        $slug = $content ?: $parent;
        
        if (!$slug) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        $contentItem = ContentItem::where('slug', $slug)
            ->where('status', 'published')
            ->with(['mediaAssets', 'parent'])
            ->first();

        if (!$contentItem) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        return response()->json([
            'content' => $contentItem,
            'message' => 'Content retrieved successfully'
        ]);
    }
}