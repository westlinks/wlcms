<?php

namespace Westlinks\Wlcms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Services\TemplateRenderer;

class ContentController extends Controller
{
    /**
     * Template renderer service.
     *
     * @var TemplateRenderer
     */
    protected TemplateRenderer $templateRenderer;

    /**
     * Constructor.
     */
    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * Display the specified content item.
     *
     * @param Request $request
     * @param string|null $parent Parent slug (for hierarchical URLs)
     * @param string|null $content Content slug
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show(Request $request, $parent = null, $content = null)
    {
        // Handle both single-level and hierarchical content URLs
        $slug = $content ?: $parent;
        
        if (!$slug) {
            abort(404, 'Content not found');
        }

        // Load content with all necessary relationships
        $contentItem = ContentItem::with([
            'templateConfig',
            'templateSettings',
            'mediaAssets',
            'parent'
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$contentItem) {
            abort(404, 'Content not found');
        }

        try {
            // Use TemplateRenderer to render the content with its template
            return $this->templateRenderer->render($contentItem);
        } catch (\Exception $e) {
            // Log error and show user-friendly message
            \Log::error('Template rendering failed', [
                'content_id' => $contentItem->id,
                'slug' => $slug,
                'template' => $contentItem->template,
                'error' => $e->getMessage()
            ]);

            // In production, show generic error; in debug, show details
            if (config('app.debug')) {
                throw $e;
            }

            abort(500, 'Error rendering page');
        }
    }
}