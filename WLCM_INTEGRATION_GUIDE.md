# WLCMS Integration Configuration for WLCM Host App

## Required Configuration Update

**CRITICAL**: Your host app needs these exact configurations:

### 1. Update your `config/wlcms.php` file:

```php
'layout' => [
    // Enable embedded mode for seamless integration
    'mode' => env('WLCMS_LAYOUT_MODE', 'embedded'),
    
    // Use your existing admin layout component
    'host_layout' => env('WLCMS_HOST_LAYOUT', 'layouts.admin-layout'),
    
    // Enable navigation integration (already implemented in your left nav)
    'navigation_integration' => env('WLCMS_NAVIGATION_INTEGRATION', true),
],

'user' => [
    // Enable user integration for proper attribution
    'model' => \App\Models\User::class,
    'name_field' => 'full_name', // Add this accessor to your User model
],
```

### 2. Add to your `.env` file:

```env
WLCMS_LAYOUT_MODE=embedded
WLCMS_HOST_LAYOUT=layouts.admin-layout  
WLCMS_NAVIGATION_INTEGRATION=true
```

### 3. Clear all caches (ESSENTIAL):

```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### 4. Add User model accessor (for firstname + lastname):

In your `app/Models/User.php`:

```php
public function getFullNameAttribute()
{
    return trim($this->firstname . ' ' . $this->lastname);
}
```

## What This Achieves

1. **Seamless Integration**: WLCMS pages will use your existing `layouts.admin-layout`
2. **Context Preservation**: Users remain in your admin interface design
3. **Navigation Consistency**: Uses your existing `<x-admin-left-nav />` component
4. **Header Integration**: Page titles appear in your existing header slot
5. **Responsive Design**: Maintains your mobile/desktop navigation behavior

## Layout Structure

Your layout expects:
- `$title` prop for page title
- `$header` slot for breadcrumbs/page headers  
- `$slot` for main content

WLCMS will provide:
- Page titles via `$title` prop
- CMS page headers via `$header` slot
- CMS content via `$slot`

## Testing the Integration

**Follow this exact sequence:**

1. **Update config and .env as shown above**
2. **Clear ALL caches**: 
   ```bash
   php artisan config:clear && php artisan view:clear && php artisan cache:clear
   ```
3. **Test configuration loading**:
   ```bash
   php artisan tinker
   config('wlcms.layout.mode')        // Should return 'embedded'  
   config('wlcms.layout.host_layout') // Should return 'layouts.admin-layout'
   ```
4. **Visit routes in this order**:
   - `/admin` - Should work (your normal admin)
   - `/admin/cms/dashboard` - Should use your layout with CMS content
   - Check navigation highlights properly with `:active` states
   - Verify responsive mobile menu works
   - Confirm dark mode compatibility

**If embedded mode still doesn't work:**
- Verify the config values load correctly in tinker
- Check Laravel logs for component errors
- Ensure `layouts.admin-layout` component exists and accepts `$title` and `$slot`

## Navigation Already Integrated

Your `admin-left-nav.blade.php` already includes:

```blade
<div class="py-2 hover:bg-slate-800 font-bold text-lg">Content Management</div>
<div class="p-1 hover:bg-slate-700">
    <x-nav-link href="{{ route('wlcms.admin.dashboard') }}" 
                :active="request()->routeIs('wlcms.admin.dashboard')">
        CMS Dashboard
    </x-nav-link>
</div>
<div class="p-1 hover:bg-slate-700">
    <x-nav-link href="{{ route('wlcms.admin.content.index') }}" 
                :active="request()->routeIs('wlcms.admin.content.*')">
        CMS Content
    </x-nav-link>
</div>
<div class="p-1 hover:bg-slate-700">
    <x-nav-link href="{{ route('wlcms.admin.media.index') }}" 
                :active="request()->routeIs('wlcms.admin.media.*')">
        CMS Media
    </x-nav-link>
</div>
```

This navigation will automatically highlight the correct sections when users navigate WLCMS pages.

## Benefits of This Approach

- ✅ No layout context loss
- ✅ Consistent styling and branding
- ✅ Mobile navigation preserved  
- ✅ Dark mode support maintained
- ✅ Breadcrumb integration
- ✅ Active navigation states work
- ✅ Zero conflicts with existing code

The WLCMS package is designed to be a good citizen in your admin ecosystem!

## ⚠️ CRITICAL WLCMS Package Issues - Confirmed January 4, 2026

**ISSUE RESOLVED**: The problem was identified and confirmed. The WLCMS package was overriding the host application's AdminLayout component.

### Root Cause Analysis
The WLCMS package registers its own `AdminLayout` component using:
```php
Blade::component('admin-layout', AdminLayout::class);
```

This **overwrites** the host application's existing `<x-admin-layout>` component, causing:
- ❌ Host admin pages lose styling and navigation  
- ❌ WLCMS package layout used instead of host layout
- ❌ Complete breakdown of host admin interface

### Required Package Fixes

**1. Component Name Conflict Resolution**
The package should NOT use `admin-layout` as the component name. Options:
- Use `wlcms-admin-layout` instead
- Use namespaced components: `<x-wlcms::admin-layout>`
- Check if component exists before registering

**2. Proper Embedded Mode Implementation**
When `layout.mode = 'embedded'`, the package should:
- NOT register any layout components
- Use the host's existing layout system  
- Render content-only views that integrate with `$hostLayout`

**3. Component Registration Logic**
```php
// WRONG - Always overwrites host component
Blade::component('admin-layout', AdminLayout::class);

// CORRECT - Conditional registration
if (config('wlcms.layout.mode') === 'standalone') {
    Blade::component('wlcms-admin-layout', AdminLayout::class);
}
// In embedded mode, don't register layout components at all
```

**4. Layout Mode Switching**
Package views should detect mode and use appropriate layout:
```blade
@if(config('wlcms.layout.mode') === 'embedded')
    <x-dynamic-component :component="config('wlcms.layout.host_layout')">
        <!-- CMS content -->
    </x-dynamic-component>
@else
    <x-wlcms-admin-layout>
        <!-- CMS content with package nav -->
    </x-wlcms-admin-layout>
@endif
```

### Integration Test Results
- **Before Fix**: `/admin` broken, `/admin/cms` worked
- **After Package Removal**: `/admin` works perfectly  
- **Conclusion**: Package was definitely causing the component override

### Action Required
**WLCMS package developers must**:
1. Rename `admin-layout` component to `wlcms-admin-layout` or use namespacing
2. Implement conditional component registration based on layout mode
3. Build proper embedded mode that respects host layout system
4. Test integration with existing Laravel applications that have their own AdminLayout components

**Status**: Ready to re-test integration once package fixes are implemented.

---

## 🔧 Troubleshooting Steps

### Problem: Routes still use standalone layout instead of embedded

**Quick Debug Checklist:**

1. **Verify config loading**:
   ```bash
   php artisan tinker
   config('wlcms.layout.mode')        // Must return 'embedded'
   config('wlcms.layout.host_layout') // Must return 'layouts.admin-layout'
   exit
   ```

2. **Check component registration**:
   ```bash
   php artisan route:list | grep cms
   # Should show routes like: GET|HEAD admin/cms/dashboard
   ```

3. **Verify .env variables are set**:
   ```bash
   grep WLCMS_ .env
   # Should show:
   # WLCMS_LAYOUT_MODE=embedded
   # WLCMS_HOST_LAYOUT=layouts.admin-layout
   ```

4. **Clear caches again** (critical after any config changes):
   ```bash
   php artisan config:clear && php artisan view:clear && php artisan cache:clear
   ```

5. **Test step by step**:
   - Visit `/admin` first - should work normally
   - Then `/admin/cms/dashboard` - should show CMS content in your layout
   - Check page source - should NOT contain WLCMS navigation, only yours

### If still not working:
- Check `storage/logs/laravel.log` for component errors
- Verify your `layouts.admin-layout` component accepts `$title` prop and `$slot`
- Try temporarily setting `WLCMS_LAYOUT_MODE=standalone` to confirm package works
- Contact for additional debugging support

### Expected Behavior:
✅ `/admin/*` routes use your layout  
✅ `/admin/cms/*` routes also use your layout but with CMS content  
✅ Navigation highlighting works  
✅ No layout context is lost

## ✅ LATEST UPDATE: Content-Only Embedded Mode (January 4, 2026)

**SOLUTION IMPLEMENTED**: True content-only embedded mode that completely removes package navigation and layout interference.

### What Changed

**1. Created Pure Content Template**
- New `content-only.blade.php` component that strips ALL package layout elements
- No wrapper divs, no styling conflicts, no navigation interference
- Just passes raw WLCMS content directly to your host layout

**2. Updated AdminLayout Component Logic**
- Embedded mode now uses content-only approach
- Zero package layout/navigation in embedded mode
- Host application maintains complete control over styling and navigation

### Key Benefits

- ✅ **Complete Package Navigation Removal**: No WLCMS sidebar, header, or layout in embedded mode
- ✅ **Zero Layout Conflicts**: Package becomes invisible - just content
- ✅ **Host Layout Control**: Your `layouts.admin-layout` handles everything
- ✅ **Seamless Integration**: WLCMS content flows naturally into your admin interface

### Technical Implementation

**Embedded Mode Flow:**
```
WLCMS Route → AdminLayout Component → content-only.blade.php → Your Host Layout → Raw Content
```

**Result:** `/admin/cms/dashboard` displays CMS content using YOUR navigation, YOUR styling, YOUR layout - with zero package interference.

### Files Modified
- `src/View/Components/AdminLayout.php` - Updated render logic
- `resources/views/components/content-only.blade.php` - New pure content template
- `resources/views/components/embedded-wrapper.blade.php` - Simplified (kept for reference)

### Testing Status
**Ready for testing** - The content-only solution addresses the core requirement of removing package navigation while maintaining full CMS functionality within your host admin layout.