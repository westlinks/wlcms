# WLCMS Layout Integration Issue

## Problem Summary
The package configuration supports `embedded` mode but the controllers and views are **NOT checking this configuration**. Even when `config('wlcms.layout.mode') === 'embedded'` is set, the package still uses its own layout instead of the host application's layout.

## Current Behavior
- ✅ Configuration loads correctly: `config('wlcms.layout.mode') = "embedded"`
- ✅ Host layout specified: `config('wlcms.layout.custom_layout') = "layouts.admin-layout"`
- ❌ Package ignores configuration and always uses its own layout
- ❌ Users are taken out of host app's admin interface

## Root Cause
The package controllers are likely doing:
```php
// Current (BROKEN) - always uses package layout
return view('wlcms::admin.dashboard');
return view('wlcms::admin.content.index');
return view('wlcms::admin.media.index');
```

Instead of checking the configuration:
```php
// What it SHOULD do - check embedded mode
if (config('wlcms.layout.mode') === 'embedded') {
    return view('wlcms::admin.dashboard-content')
        ->extends(config('wlcms.layout.custom_layout'));
} else {
    return view('wlcms::admin.dashboard');
}
```

## Required Fixes

### 1. Update Controllers
**Files to modify:**
- `src/Http/Controllers/Admin/DashboardController.php`
- `src/Http/Controllers/Admin/ContentController.php`
- `src/Http/Controllers/Admin/MediaController.php`

**Pattern to implement:**
```php
class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'stats' => $this->getStats(),
            'recentContent' => $this->getRecentContent(),
        ];

        // Check layout mode configuration
        if (config('wlcms.layout.mode') === 'embedded') {
            // Use content-only view with host layout
            return view('wlcms::admin.dashboard-content', $data)
                ->extends(config('wlcms.layout.custom_layout'));
        }
        
        // Use full package layout (default)
        return view('wlcms::admin.dashboard', $data);
    }
}
```

### 2. Create Content-Only Views
**Files to create:**
- `resources/views/admin/dashboard-content.blade.php`
- `resources/views/admin/content/index-content.blade.php`
- `resources/views/admin/content/create-content.blade.php`
- `resources/views/admin/content/edit-content.blade.php`
- `resources/views/admin/media/index-content.blade.php`

**Pattern for content-only views:**
```blade
{{-- resources/views/admin/dashboard-content.blade.php --}}
{{-- Content-only version for embedded mode --}}

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('CMS Dashboard') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Dashboard content without layout wrapper --}}
        @include('wlcms::admin.dashboard-content-partial')
    </div>
</div>
```

### 3. Create Shared Content Partials
**Files to create:**
- `resources/views/admin/partials/dashboard-content.blade.php`
- `resources/views/admin/partials/content-index.blade.php`
- `resources/views/admin/partials/media-index.blade.php`

**Purpose:** Extract the actual content from existing views so it can be shared between full layout and content-only versions.

### 4. Update Existing Views
**Modify existing views to use partials:**
```blade
{{-- resources/views/admin/dashboard.blade.php --}}
@extends('wlcms::layouts.admin')

@section('content')
    @include('wlcms::admin.partials.dashboard-content')
@endsection
```

### 5. Alternative: Single View with Conditional Layout

**Simpler approach - modify existing views:**
```blade
{{-- At top of existing views like dashboard.blade.php --}}
@if(config('wlcms.layout.mode') === 'embedded')
    @extends(config('wlcms.layout.custom_layout'))
@else
    @extends('wlcms::layouts.admin')
@endif

@section('content')
    {{-- Existing content here --}}
@endsection
```

## Recommended Implementation Approach

### Option A: Dual Views (More Work, Cleaner)
1. Create content-only versions of all admin views
2. Update controllers to choose between full/content views
3. Extract shared content to partials

### Option B: Conditional Layout (Less Work, Quick Fix)
1. Modify existing views to conditionally extend layouts
2. No controller changes needed
3. Single view files handle both modes

## Files Requiring Changes

### Controllers (Option A only):
```
src/Http/Controllers/Admin/DashboardController.php
src/Http/Controllers/Admin/ContentController.php
src/Http/Controllers/Admin/MediaController.php
```

### Views (Option A):
```
resources/views/admin/dashboard-content.blade.php (NEW)
resources/views/admin/content/index-content.blade.php (NEW)
resources/views/admin/content/create-content.blade.php (NEW)
resources/views/admin/content/edit-content.blade.php (NEW)
resources/views/admin/media/index-content.blade.php (NEW)
resources/views/admin/partials/dashboard-content.blade.php (NEW)
resources/views/admin/partials/content-index.blade.php (NEW)
resources/views/admin/partials/media-index.blade.php (NEW)
```

### Views (Option B - Simpler):
```
resources/views/admin/dashboard.blade.php (MODIFY)
resources/views/admin/content/index.blade.php (MODIFY)
resources/views/admin/content/create.blade.php (MODIFY)
resources/views/admin/content/edit.blade.php (MODIFY)
resources/views/admin/media/index.blade.php (MODIFY)
```

## Testing Integration

After implementing, test with:

1. **Standalone Mode:**
   ```bash
   # In .env: WLCMS_LAYOUT_MODE=standalone
   # Should show package's own admin layout
   ```

2. **Embedded Mode:**
   ```bash
   # In .env: WLCMS_LAYOUT_MODE=embedded
   # In .env: WLCMS_CUSTOM_LAYOUT=layouts.admin-layout
   # Should show content within host app's admin layout
   ```

## Expected Outcome

After fix:
- ✅ Embedded mode respects host app layout
- ✅ Navigation remains consistent with host app
- ✅ Top nav and left nav preserve host app styling
- ✅ Only main content area shows WLCMS functionality
- ✅ Standalone mode continues working as before

---

**Priority:** Critical for package adoption
**Effort:** Medium (2-4 hours depending on approach)
**Impact:** Solves major integration barrier for Laravel applications