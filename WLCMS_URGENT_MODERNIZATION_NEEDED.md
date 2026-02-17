# URGENT: WLCMS Template Architecture Must Be Modernized

**To:** WLCMS Development Team  
**From:** Host Application Integration Team  
**Date:** February 17, 2026  
**Priority:** CRITICAL - Blocking Production Deployment

---

## Executive Summary

**The WLCMS package cannot be integrated with modern Laravel applications because it uses deprecated template syntax.**

Your templates use `@extends` and `@yield`, which were the standard in Laravel 5-6 but have been **replaced by Blade components** (`<x-component>` and `{{ $slot }}`) since Laravel 7 (released in 2020).

**Impact:** Host applications cannot include their navigation, authentication, or any layout components when rendering WLCMS content. Pages appear isolated and broken.

**Required Action:** Refactor template architecture to support modern Blade component syntax.

---

## The Technical Problem

### Your Current Template Architecture

```blade
{{-- vendor/westlinks/wlcms/resources/views/templates/full-width.blade.php --}}
@extends('wlcms::layouts.base')

@section('content')
    <div class="container">
        {!! $zones['content'] ?? '' !!}
    </div>
@endsection
```

```blade
{{-- vendor/westlinks/wlcms/resources/views/layouts/base.blade.php --}}
<!DOCTYPE html>
<html>
<body>
    @yield('content')  <!-- This is the problem -->
</body>
</html>
```

### Modern Laravel Applications (Laravel 7+)

```blade
{{-- Host app: resources/views/layouts/app-layout.blade.php --}}
<x-app-layout>
    <x-navigation-menu />
    
    <main>
        {{ $slot }}  <!-- Modern component syntax -->
    </main>
</x-app-layout>
```

### Why They're Incompatible

- **@extends/@yield** requires the parent layout to use `@yield('section-name')`
- **Components/slots** require the parent to use `{{ $slot }}`
- **You cannot mix them** - a template using `@extends` cannot pass content to a component's `{{ $slot }}`
- Laravel components don't have "sections" - they have a single `$slot`

### What Happens When We Try to Integrate

```php
// Host app controller
public function show($slug) {
    $content = ContentItem::find($slug);
    return $this->templateRenderer->render($content, [
        'layout' => 'layouts.app-layout'  // Modern component
    ]);
}
```

**Result:** The template still uses `@extends('wlcms::layouts.base')` and ignores the layout parameter completely. The host app's navigation never appears.

---

## Required Changes to WLCMS Package

### Change 1: Update TemplateRenderer Service

**File:** `src/Services/TemplateRenderer.php`

Add support for passing a layout component that templates can use:

```php
public function render(ContentItem $content, array $options = []): View
{
    // ... existing code for template resolution, zones, settings ...
    
    // NEW: Allow host app to specify layout component
    $layoutComponent = $options['layout'] ?? 'wlcms::layouts.base';
    
    $viewData = [
        'contentItem' => $content,
        'content' => $content,
        'template' => $template,
        'settings' => $settings,
        'zones' => $zones,
        'meta' => $meta,
        'layout' => $layoutComponent, // Make available to templates
    ];
    
    return view($template->view_path, $viewData);
}
```

### Change 2: Update Base Layout to Use Component Syntax

**File:** `resources/views/layouts/base.blade.php`

**Before (Deprecated):**
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? 'Page' }}</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

**After (Modern):**
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? 'Page' }}</title>
</head>
<body>
    {{ $slot }}
</body>
</html>
```

### Change 3: Refactor ALL Template Files

You need to update all 8 template files to use the dynamic component pattern:

**Before (Current - Deprecated):**
```blade
{{-- templates/full-width.blade.php --}}
@extends('wlcms::layouts.base')

@section('content')
<div class="container">
    <article class="main-content full-width-template">
        @if(($settings['show_featured_image'] ?? 'no') === 'yes' && isset($settings['featured_image']))
        <div class="featured-image" style="margin-bottom: 2rem;">
            <img src="{{ $settings['featured_image'] }}" alt="{{ $contentItem->title }}">
        </div>
        @endif

        <div class="content-zone">
            {!! $zones['content'] ?? '' !!}
        </div>
    </article>
</div>
@endsection
```

**After (Modern):**
```blade
{{-- templates/full-width.blade.php --}}
<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
    <div class="container">
        <article class="main-content full-width-template">
            @if(($settings['show_featured_image'] ?? 'no') === 'yes' && isset($settings['featured_image']))
            <div class="featured-image" style="margin-bottom: 2rem;">
                <img src="{{ $settings['featured_image'] }}" alt="{{ $contentItem->title }}">
            </div>
            @endif

            <div class="content-zone">
                {!! $zones['content'] ?? '' !!}
            </div>
        </article>
    </div>
</x-dynamic-component>
```

### Templates That Need Updating

1. ✅ `resources/views/templates/full-width.blade.php`
2. ✅ `resources/views/templates/sidebar-right.blade.php`
3. ✅ `resources/views/templates/contact-form.blade.php`
4. ✅ `resources/views/templates/event-landing-page.blade.php`
5. ✅ `resources/views/templates/event-registration.blade.php`
6. ✅ `resources/views/templates/signup-form-page.blade.php`
7. ✅ `resources/views/templates/time-limited-content.blade.php`
8. ✅ `resources/views/templates/archive-timeline.blade.php`

**Pattern for each:**
1. Remove `@extends('wlcms::layouts.base')`
2. Remove `@section('content')` and `@endsection`
3. Wrap entire content in `<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">`
4. Add closing `</x-dynamic-component>`

---

## How It Will Work After Modernization

### Host Application Usage

```php
<?php
// In host app controller
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
        $content = ContentItem::with([
            'templateConfig',
            'templateSettings',
            'mediaAssets'
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Pass host app's modern layout component
        return $this->templateRenderer->render($content, [
            'layout' => 'layouts.app-layout'
        ]);
    }
}
```

### Result

The WLCMS template content will render **inside** the host app's layout, with:
- ✅ Host app navigation
- ✅ Host app authentication
- ✅ Host app footer
- ✅ Host app styles and scripts
- ✅ Seamless integration

---

## Testing the Changes

### Unit Test

```php
<?php

use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Services\TemplateRenderer;

test('templates render with custom layout component', function () {
    $content = ContentItem::factory()->create([
        'template' => 'full-width',
        'title' => 'Test Page',
    ]);
    
    $renderer = app(TemplateRenderer::class);
    
    // Render with custom layout
    $view = $renderer->render($content, [
        'layout' => 'test-layout'
    ]);
    
    $html = $view->render();
    
    // Should contain content
    expect($html)->toContain('Test Page');
    
    // Should use the specified layout
    expect($html)->toContain('test-layout');
});
```

### Integration Test

```php
test('templates work with host app navigation', function () {
    $content = ContentItem::factory()->create([
        'slug' => 'test-page',
        'status' => 'published',
    ]);
    
    $response = $this->get('/test-page');
    
    $response->assertOk();
    $response->assertSee('navigation-menu'); // Host app nav
    $response->assertSee('content-zone'); // WLCMS template
});
```

---

## Migration Path for Existing Users

### Option 1: Breaking Change (Recommended)

Make this a major version bump (v3.0) and require users to update their code. Most users won't notice because:
- The default behavior is unchanged (uses `wlcms::layouts.base`)
- Templates still work exactly the same way
- Only integrators passing custom layouts are affected

### Option 2: Backward Compatibility

If you want to maintain backward compatibility temporarily:

```blade
{{-- In each template --}}
@if(isset($layout))
    {{-- New component-based approach --}}
    <x-dynamic-component :component="$layout">
        @include('wlcms::templates.partials.full-width-content')
    </x-dynamic-component>
@else
    {{-- Old @extends approach (deprecated) --}}
    @extends('wlcms::layouts.base')
    @section('content')
        @include('wlcms::templates.partials.full-width-content')
    @endsection
@endif
```

Then add to docs:
> **Deprecated:** The `@extends` pattern is deprecated and will be removed in v4.0. Please migrate to component-based layouts.

---

## Why This Matters

### Industry Standard

- Laravel 7+ (March 2020) introduced Blade components as the standard
- Laravel 8, 9, 10, 11 all use component-based architecture
- **We're now in 2026** - this is 6 years old
- Stack Overflow, Laravel docs, tutorials all show component syntax
- Jetstream, Breeze, Livewire all use components

### Developer Expectations

Modern Laravel developers expect:
- Component-based templates
- `<x-slot>` syntax
- Composable, reusable components
- Not `@extends` and `@yield`

### Package Ecosystem

Other modern Laravel packages support component integration:
- Filament: Component-based
- Livewire: Component-based
- Jetstream: Component-based
- Tall Stack: Component-based

WLCMS needs to align with the ecosystem.

---

## Timeline Request

This is blocking our production deployment. Our application cannot go live with CMS pages that lack navigation.

**Can you provide:**
1. **Acknowledgment** - Are you able to make these changes?
2. **Timeline** - When can we expect the updated package?
3. **Version** - Will this be v3.0 or a minor patch?

**Our timeline:** We need this resolved within the next sprint (2 weeks) to meet our launch deadline.

---

## Alternative Solutions We Considered

### Why We Can't Work Around This

1. **❌ Duplicate layouts** - Creates maintenance nightmare, layouts get out of sync
2. **❌ Render wrapping** - Adds complexity, performance overhead, breaks meta tags
3. **❌ Mix both syntaxes** - Technically impossible, Laravel doesn't support it
4. **❌ Modify our app to use @yield** - Forces our entire modern app to use deprecated syntax
5. **❌ Don't use WLCMS templates** - Defeats the purpose of using your package

**The only solution is to modernize WLCMS to use component syntax.**

---

## Questions?

If you have questions about:
- Why component syntax is needed
- How `<x-dynamic-component>` works
- Integration testing requirements
- Migration strategy for existing users
- Timeline constraints

Please reach out immediately. We're happy to provide:
- Code examples
- Pair programming session
- Testing assistance
- Documentation updates

---

## Summary Checklist

To unblock our integration, WLCMS needs:

- [ ] Update `TemplateRenderer` to pass `$layout` to templates
- [ ] Update `layouts/base.blade.php` to use `{{ $slot }}`
- [ ] Update all 8 template files to use `<x-dynamic-component>`
- [ ] Add unit tests for layout component support
- [ ] Add integration tests with custom layouts
- [ ] Update documentation with new integration pattern
- [ ] Publish new package version

**Once complete, our integration is 5 lines of code:**
```php
return $this->templateRenderer->render($content, [
    'layout' => 'layouts.app-layout'
]);
```

---

**Contact:** Host Application Integration Team  
**Urgency:** Critical - Blocking Production Launch  
**Submitted:** February 17, 2026

We appreciate your prompt attention to this matter and look forward to continuing to use WLCMS as our CMS solution.
