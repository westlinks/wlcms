# WLCMS Public Template Rendering Guide

**Date:** February 17, 2026  
**Phase:** 6.5 - Public Template Rendering  
**Status:** ✅ Complete

## Overview

This document explains how to render WLCMS content items on public-facing pages with proper template support. The system automatically resolves templates, processes zone data, and renders the appropriate view.

## Quick Start

### Option 1: Use WLCMS Routes (Recommended for Simple Sites)

Enable the built-in frontend routes in your config:

```php
// config/wlcms.php
return [
    'frontend' => [
        'enabled' => true,
    ],
];
```

WLCMS will register routes at `/cms-content/{slug}` that automatically handle template rendering.

**Route Pattern:**
- `/cms-content/about-us` → Renders content with slug 'about-us'
- `/cms-content/services` → Renders content with slug 'services'

### Option 2: Custom Controller (Recommended for Navigation Integration)

For full control (like rendering content in navigation), use `TemplateRenderer` directly:

```php
<?php

namespace App\Http\Controllers;

use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Services\TemplateRenderer;

class WlcmsPageController extends Controller
{
    protected TemplateRenderer $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function show(string $slug)
    {
        // Load content with all template relationships
        $content = ContentItem::with([
            'templateConfig',
            'templateSettings',
            'mediaAssets'
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Render with template
        return $this->templateRenderer->render($content);
    }
}
```

**Register Your Route:**

```php
// routes/web.php
Route::get('/{slug}', [WlcmsPageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('wlcms.page.show');
```

## How It Works

### 1. Template Resolution

The system automatically resolves templates in this order:

1. **Relationship**: Checks `$content->templateConfig` (eager loaded relationship)
2. **Database Lookup**: Falls back to `Template::where('identifier', $content->template)->first()`
3. **Default Fallback**: Uses 'full-width' template as last resort

### 2. Data Passed to Views

Every template receives:

```php
[
    'contentItem' => ContentItem,    // Full content model
    'content' => ContentItem,        // Alias for backward compatibility
    'template' => Template,          // Template configuration
    'settings' => array,             // Template-specific settings
    'zones' => array,                // Processed zone content
    'meta' => array,                 // SEO/meta data
]
```

### 3. Zone Data Processing

Zones are automatically processed based on their type:

- **rich_text**: Rendered as HTML (safe output)
- **repeater**: Array of items
- **image**: Media asset URLs
- **select/radio**: Selected value

Templates access zones like:
```blade
{!! $zones['content'] ?? '' !!}
{!! $zones['sidebar'] ?? '' !!}
```

### 4. Template Settings

Custom template settings are available as `$settings`:

```blade
@if($settings['show_featured_image'] ?? false)
    <img src="{{ $contentItem->featuredImageUrl }}" alt="{{ $contentItem->title }}">
@endif

<div class="hero" style="background-color: {{ $settings['hero_background'] ?? '#ffffff' }}">
    ...
</div>
```

## Error Handling

The system gracefully handles errors:

- **Content Not Found**: Returns 404
- **Template Not Found**: Falls back to 'full-width' template, or throws exception if unavailable
- **Rendering Errors**: Logged to Laravel log; shows generic error (or full error in debug mode)

### Custom Error Handling

```php
use Westlinks\Wlcms\Services\TemplateRenderer;

try {
    return $this->templateRenderer->render($content);
} catch (\Exception $e) {
    \Log::error('Template render failed', [
        'content_id' => $content->id,
        'error' => $e->getMessage()
    ]);
    
    return view('errors.template-error', compact('content'));
}
```

## Working with Layouts

Templates extend `wlcms::layouts.base` by default, which provides:

- SEO meta tags
- Breadcrumbs support
- Featured image handling
- Responsive container

### Using Custom Layout

If you want to use your app's layout:

**Option A: Override in Template View**

```blade
{{-- In your template view file --}}
@extends('layouts.app')

@section('content')
    <h1>{{ $contentItem->title }}</h1>
    {!! $zones['content'] ?? '' !!}
@endsection
```

**Option B: Pass Custom Layout**

```php
return $this->templateRenderer->render($content, [
    'layout' => 'layouts.app'
]);
```

## Navigation Integration

To render CMS pages in navigation (answering the host app's specific need):

```php
// In your NavigationHelper or similar
use Westlinks\Wlcms\Models\ContentItem;

$navigationItems = [
    ['slug' => 'about-us', 'label' => 'About Us'],
    ['slug' => 'services', 'label' => 'Services'],
];

foreach ($navigationItems as $item) {
    $content = ContentItem::where('slug', $item['slug'])->first();
    if ($content) {
        // Generate URL for content
        $url = route('wlcms.page.show', ['slug' => $content->slug]);
        // Or use your own route
    }
}
```

## Available Templates

Current template library (as of Phase 6):

**Basic Templates:**
- `full-width` - Full-width single column
- `sidebar-right` - Main content with right sidebar
- `sidebar-left` - Main content with left sidebar  
- `two-column` - Equal two-column layout

**Advanced Templates:**
- `event-registration` - Event pages with registration form
- `signup-form-page` - Dedicated signup/registration pages
- `time-limited-content` - Content with countdown timer
- `archive-timeline` - Chronological archive display

## Testing Template Rendering

```php
// Test in Tinker
php artisan tinker

$content = \Westlinks\Wlcms\Models\ContentItem::find(1);
$renderer = app(\Westlinks\Wlcms\Services\TemplateRenderer::class);
$view = $renderer->render($content);
echo $view->render();
```

## Frequently Asked Questions

### Q: Is there a service/helper for template resolution?

**A:** Yes! Use `TemplateRenderer` service (shown above). It handles all resolution, fallbacks, zone processing, and rendering.

### Q: Should WlcmsPageController be in the WLCMS package or host app?

**A:** Either works:
- **In Package**: Use built-in routes by enabling `wlcms.frontend.enabled`
- **In Host App**: Use custom controller for more control (recommended for navigation integration)

### Q: What's the intended route registration pattern?

**A:** For catch-all routes, use:
```php
Route::get('/{slug}', [WlcmsPageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+');
```

Place this **after** your specific routes to avoid conflicts.

### Q: Are there base layout expectations?

**A:** Templates extend `wlcms::layouts.base` by default, but you can:
- Override in template view with `@extends('your.layout')`
- Publish views: `php artisan vendor:publish --tag=wlcms-views`
- Customize the base layout

## Troubleshooting

### Template Not Rendering

**Check:**
1. Content has valid `template` field: `$content->template`
2. Template exists in database: `Template::where('identifier', $content->template)->exists()`
3. View file exists: `View::exists($template->view_path)`

### Zones Empty

**Check:**
1. Template settings saved: `$content->templateSettings()->exists()`
2. Zones data present: `$content->templateSettings->zones_data`

### Settings Not Applied

**Check:**
1. Settings saved in database: `$content->templateSettings->settings`
2. Template schema matches: Compare with `$template->settings_schema`

## Next Steps

- Phase 7: Auto-Activation System (time-based publishing)
- Phase 8: Form Embedding Integration
- Future: Dynamic routing, custom post types, taxonomy support

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Enable debug mode: `APP_DEBUG=true` in `.env`
- Review requirements: `WLCMS_Template_System_Requirements.md`
