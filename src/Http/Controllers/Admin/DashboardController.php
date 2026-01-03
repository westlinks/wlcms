<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\MediaAsset;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_content' => ContentItem::count(),
            'published_content' => ContentItem::where('status', 'published')->count(),
            'draft_content' => ContentItem::where('status', 'draft')->count(),
            'total_media' => MediaAsset::count(),
            'recent_content' => ContentItem::latest()->take(5)->get(),
            'recent_media' => MediaAsset::latest()->take(5)->get(),
        ];

        return view('wlcms::admin.dashboard', compact('stats'));
    }
}