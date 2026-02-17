# WLCMS Template Integration Requirements

**Date:** February 17, 2026  
**From:** WLCM Host Application Team  
**To:** WLCMS Package Development Team

## Context

We acknowledge that WLCMS is under active development. We're implementing the navigation system to use CMS content items and need proper template rendering support for public-facing pages.

## Current State

**What Works:**
- CMS content items displaying in navigation (using NavigationHelper)
- Basic page rendering at `/{slug}` routes via WlcmsPageController
- Admin interface for creating/editing content with template selection
- Template system is well-defined in database (8 templates in `cms_templates`)
- Template view files exist in `vendor/westlinks/wlcms/resources/views/templates/`

**What Doesn't Work:**
- WlcmsPageController ignores the content's assigned template
- Currently hardcoded to use `wlcms.templates.default.page`
- Zone data and template settings not being passed to views

## What We Need

### 1. Template Resolution
When rendering content at `WlcmsPageController::show($slug)`, we need to:

1. Read the `template` field from `cms_content_items` (e.g., 'full-width', 'sidebar-right')
2. Resolve to the correct view path from `cms_templates.view_path` (e.g., 'wlcms::templates.full-width')
3. Load that template instead of hardcoded default

### 2. Zone Data
Templates define zones in their `zones` column (JSON):
```json
{
  "content": {"label": "Main Content", "type": "rich_text", "required": true},
  "sidebar": {"label": "Sidebar Content", "type": "rich_text", "required": false}
}
```

The actual zone content is stored in `cms_content_template_settings.zones_data` and needs to be:
- Retrieved via `$content->templateSettings->zones_data`
- Passed to view as `$zones` array
- Templates render zones like: `{!! $zones['content'] ?? '' !!}`

### 3. Template Settings
Custom settings (show_featured_image, hero_background, etc.) from `cms_content_template_settings.settings` need to be passed as `$settings` array to templates.

### 4. Required View Data
Templates need these variables:
```php
return view($templateViewPath, [
    'content' => $content,           // ContentItem model
    'zones' => $zonesData,           // Array of zone content
    'settings' => $settingsData,     // Array of template-specific settings
    'contentItem' => $content,       // Some templates use this name
]);
```

## Example Flow

**Current (Broken):**
```php
// WlcmsPageController::show()
$content = ContentItem::where('slug', $slug)->first();
return view('wlcms.templates.default.page', compact('content')); // ❌ Ignores template
```

**Needed:**
```php
// WlcmsPageController::show()
$content = ContentItem::with('templateSettings')->where('slug', $slug)->first();

// Get template view path from database
$template = Template::where('identifier', $content->template)->first();
$viewPath = $template->view_path; // 'wlcms::templates.sidebar-right'

// Extract zones and settings
$zones = $content->templateSettings->zones_data ?? [];
$settings = $content->templateSettings->settings ?? [];

return view($viewPath, [
    'content' => $content,
    'contentItem' => $content,
    'zones' => $zones,
    'settings' => $settings,
]);
```

## Database References

**Tables:**
- `cms_templates` - Template definitions with zones, view_path, settings_schema
- `cms_content_items` - Content with `template` field (identifier like 'full-width')
- `cms_content_template_settings` - Per-content zones_data and settings (JSON columns)

**Test Data:**
- Content ID 5 uses 'sidebar-right' template
- Content IDs 1-4 use 'full-width' template

## Integration Point

The fix should be in the WLCMS package's public-facing controller/service, not in our host app's WlcmsPageController. We're calling the package expecting it to handle template resolution.

Alternatively, if you prefer the host app to handle this, we need documentation on the expected pattern for:
1. Loading template definitions
2. Resolving view paths
3. Extracting and formatting zones/settings data

## Questions for WLCMS Team

1. Is there a service/helper in WLCMS for template resolution we should use?
2. Should WlcmsPageController be in the WLCMS package instead of host app?
3. What's the intended public route registration pattern? (We're using catch-all `/{slug}`)
4. Are there base layout expectations? (Currently using `x-guest-layout`, templates extend `wlcms::layouts.base`)

## Timeline

Navigation integration is in progress. Template support needed for full content display functionality. We can work with basic templates first (full-width, sidebar-right) before tackling complex ones (event-landing-page, time-limited-content).

---

**Contact:** See conversation history in WLCM repo dev branch for full context of navigation implementation.
