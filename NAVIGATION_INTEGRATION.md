# WLCMS Navigation Integration

This document explains how to integrate WLCMS navigation into existing Laravel admin interfaces.

## Overview

WLCMS provides flexible navigation integration to work with existing admin panels instead of forcing its own standalone interface. This makes the package much more adoptable for real-world Laravel applications.

## Configuration

### Layout Mode

Set the layout mode in your `.env` file:

```env
# Standalone mode (default) - full WLCMS admin interface
WLCMS_LAYOUT_MODE=standalone

# Embedded mode - content-only views for integration
WLCMS_LAYOUT_MODE=embedded

# Enable navigation integration helpers
WLCMS_NAVIGATION_INTEGRATION=true

# Custom layout for embedded views (optional)
WLCMS_CUSTOM_LAYOUT=layouts.admin

# Route prefix for embedded mode
WLCMS_EMBEDDED_PREFIX=cms
```

### Icon Library

Configure the icon system to match your admin interface:

```env
# Icon library: heroicons, fontawesome, lucide
WLCMS_ICON_TYPE=heroicons
WLCMS_ICON_PREFIX=heroicon-o-
```

## Integration Examples

### Laravel Nova Integration

```php
// In your Nova service provider
use Westlinks\Wlcms\Support\NavigationHelper;

public function boot()
{
    Nova::mainMenu(function (Request $request) {
        return [
            // Your existing menu items...
            
            // Add WLCMS navigation
            MenuSection::make('Content Management', 
                NavigationHelper::getNovaNavigationItems()
            ),
        ];
    });
}
```

### Filament Integration

```php
// In your Filament panel provider
use Westlinks\Wlcms\Support\NavigationHelper;

public function panel(Panel $panel): Panel
{
    return $panel
        ->navigationGroups([
            'Content Management' => NavigationHelper::getFilamentNavigationItems(),
            // Your other groups...
        ]);
}
```

### Custom Admin Interface

```php
// In your custom admin layout
use Westlinks\Wlcms\Support\NavigationHelper;

// Get navigation items
$wlcmsNavigation = NavigationHelper::getCustomNavigationItems();

// Or render HTML directly
echo NavigationHelper::renderCustomNavigation([
    'wrapper_class' => 'sidebar-menu',
    'item_class' => 'menu-item',
    'link_class' => 'menu-link'
]);
```

## View Integration

### Embedding Views in Your Layout

```blade
{{-- In your admin layout --}}
@extends('layouts.admin')

@section('content')
<div class="admin-content">
    <h1>Content Management</h1>
    
    {{-- Embed WLCMS content view --}}
    @include('wlcms::admin.content.index-content')
</div>
@endsection
```

### Using Embedded Mode

When `WLCMS_LAYOUT_MODE=embedded`, WLCMS views automatically return content-only versions:

```php
// This will return content-only view when in embedded mode
return view('wlcms::admin.content.index', compact('content'));
```

## Available Views

### Content-Only Views (for embedding)
- `wlcms::admin.dashboard-content`
- `wlcms::admin.content.index-content`
- `wlcms::admin.media.index-content`

### Full Layout Views (standalone mode)
- `wlcms::admin.dashboard`
- `wlcms::admin.content.index`
- `wlcms::admin.media.index`

## Navigation Helper Methods

```php
use Westlinks\Wlcms\Support\NavigationHelper;

// Get raw navigation items
$items = NavigationHelper::getNavigationItems();

// Format for specific admin interfaces
$nova = NavigationHelper::getNovaNavigationItems();
$filament = NavigationHelper::getFilamentNavigationItems();
$custom = NavigationHelper::getCustomNavigationItems();

// Get permissions list
$permissions = NavigationHelper::getPermissions();

// Check configuration
$isEmbedded = NavigationHelper::isEmbeddedMode();
$integrationEnabled = NavigationHelper::isNavigationIntegrationEnabled();

// Map icons to your icon library
$icon = NavigationHelper::mapIcon('document-text', 'fontawesome');
```

## Permissions Integration

WLCMS provides standard permissions that can be integrated with your existing permission system:

```php
// Get WLCMS permissions
$permissions = NavigationHelper::getPermissions();

// Example with Spatie Laravel Permission
foreach ($permissions as $permission => $description) {
    Permission::firstOrCreate(['name' => $permission]);
}

// In your middleware or gate
Gate::define('wlcms.manage_content', function ($user) {
    return $user->can('manage content');
});
```

## Customization

### Custom Navigation Config

Publish and modify the navigation configuration:

```bash
php artisan vendor:publish --tag=wlcms-navigation
```

Edit `config/wlcms-navigation.php` to customize navigation items, icons, permissions, and integration settings.

### Custom Layout Integration

Create your own layout wrapper:

```blade
{{-- resources/views/layouts/cms.blade.php --}}
@extends('layouts.admin')

@section('title', $title ?? 'CMS')

@section('content')
<div class="cms-wrapper">
    <div class="cms-header">
        <h1>{{ $pageTitle ?? 'Content Management' }}</h1>
    </div>
    
    <div class="cms-content">
        @yield('cms-content')
    </div>
</div>
@endsection
```

Then set in your environment:
```env
WLCMS_CUSTOM_LAYOUT=layouts.cms
```

## Benefits

1. **No Layout Conflicts**: Embed WLCMS into existing admin interfaces
2. **Consistent UX**: Users don't need to learn a separate admin interface
3. **Permission Integration**: Works with existing permission systems
4. **Icon Consistency**: Maps to your existing icon library
5. **Flexible Integration**: Works with Nova, Filament, or custom admin panels

## Migration from Standalone

To migrate from standalone to embedded mode:

1. Set `WLCMS_LAYOUT_MODE=embedded` in your `.env`
2. Add navigation items to your existing admin interface
3. Update any direct links to use your admin's routing structure
4. Test all functionality in embedded mode

The views will automatically switch to content-only versions without breaking existing functionality.