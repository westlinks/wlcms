# Modern Laravel Layout Integration Guide

## Overview
WLCMS now uses modern Laravel component-based layouts instead of the legacy @extends/@include pattern. This provides better flexibility, maintainability, and integration capabilities.

## Component Architecture

### AdminLayout Component
Location: `src/View/Components/AdminLayout.php`

The component automatically detects the layout mode from configuration:
- **Standalone mode**: Uses full WLCMS layout with navigation, header, and sidebar
- **Embedded mode**: Integrates seamlessly into existing host application layouts

### Blade Components
- `resources/views/components/admin-layout.blade.php` - Main layout component  
- `resources/views/components/embedded-wrapper.blade.php` - Embedded mode wrapper

## Modern Syntax

### Before (Legacy)
```blade
@extends('wlcms::admin.layout')

@section('title', 'Page Title')
@section('page-title', 'Page Header')

@section('content')
    <!-- Page content -->
@endsection
```

### After (Modern)
```blade
<x-wlcms::admin-layout title="Page Title" page-title="Page Header">
    <!-- Page content -->
</x-wlcms::admin-layout>
```

## Configuration

### Layout Mode
Set in `config/wlcms.php`:

```php
'layout' => [
    'mode' => 'standalone', // or 'embedded'
    'host_layout' => 'layouts.admin', // for embedded mode
],
```

### Navigation Integration
For embedded mode, the component uses `x-dynamic-component` to load the host layout:

```blade
<x-dynamic-component 
    :component="config('wlcms.layout.host_layout', 'layouts.admin')"
    title="{{ $title }}"
>
    <x-slot name="content">
        {{ $slot }}
    </x-slot>
</x-dynamic-component>
```

## Benefits

### 1. Modern Laravel Compliance
- Uses Laravel's component system (Laravel 7+)
- Follows current Laravel best practices
- Better IDE support and syntax highlighting

### 2. Enhanced Flexibility
- Dynamic layout selection based on configuration
- Easy switching between standalone and embedded modes
- Clean component composition

### 3. Better Integration
- Seamless embedding into existing admin interfaces
- Maintains host application styling and navigation
- No conflicts with existing layouts

### 4. Improved Maintainability
- Single component for layout logic
- Centralized layout mode detection
- Easier to extend and customize

## Updated Views

All admin views have been modernized:
- ✅ `admin/dashboard.blade.php`
- ✅ `admin/content/index.blade.php`
- ✅ `admin/content/create.blade.php`
- ✅ `admin/content/edit.blade.php`
- ✅ `admin/content/show.blade.php`
- ✅ `admin/media/index.blade.php`

## Controller Updates

Controllers have been enhanced to provide proper data for the modern views:
- ✅ `DashboardController` - Improved stats with relationships
- ✅ `ContentController` - Added filtering and pagination
- ✅ `MediaController` - Enhanced folder navigation

## Migration from Legacy

If you have custom views using the old pattern:

1. Replace `@extends('wlcms::admin.layout')` with `<x-wlcms::admin-layout>`
2. Convert `@section('title')` to `title="..."` attribute
3. Convert `@section('page-title')` to `page-title="..."` attribute
4. Move content from `@section('content')` to inside the component tags
5. Remove `@endsection` and close with `</x-wlcms::admin-layout>`

## Testing

Use the test layout view to verify integration:
```
/admin/test-layout (add route as needed)
```

The test page shows:
- Component system status
- Current layout mode
- Navigation functionality
- Integration success

## Troubleshooting

### Component Not Found
Ensure the service provider is registered and component namespace is available:
```php
Blade::componentNamespace('Westlinks\\Wlcms\\View\\Components', 'wlcms');
```

### Layout Mode Issues
Check configuration:
```php
config('wlcms.layout.mode')  // Should be 'standalone' or 'embedded'
config('wlcms.layout.host_layout')  // Should be valid component name
```

### Embedded Mode Problems
1. Verify host layout component exists
2. Ensure host layout accepts `title` prop and `content` slot
3. Check for CSS/JS conflicts

This modern approach ensures WLCMS stays current with Laravel best practices while providing maximum flexibility for integration scenarios.