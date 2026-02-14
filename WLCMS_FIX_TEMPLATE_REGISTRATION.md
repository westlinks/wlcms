# WLCMS Fix: Template Registration Log Noise

## Issue
Production logs on wlbfm (running against wleps database) showing repeated DEBUG messages:
```
Template registration skipped: SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'wleps.cms_templates' doesn't exist
```

## Root Cause
- `WlcmsServiceProvider` runs on every request boot
- Tries to persist templates to `cms_templates` table
- Table doesn't exist in `wleps` database (CMS not installed there)
- Exception is caught but logged as DEBUG noise

## File to Edit
**Location**: `src/WlcmsServiceProvider.php` (lines 291-298)

## Proposed Fix
Replace the current code:
```php
// Persist registered templates to database
if (config('wlcms.templates.auto_persist', true)) {
    try {
        \Westlinks\Wlcms\Services\TemplateManager::persistRegisteredTemplates();
    } catch (\Exception $e) {
        // Silent fail during initial installation when tables don't exist yet
        \Illuminate\Support\Facades\Log::debug('Template registration skipped: ' . $e->getMessage());
    }
}
```

With:
```php
// Persist registered templates to database
if (config('wlcms.templates.auto_persist', true)) {
    // Only attempt to persist if the cms_templates table exists
    if (\Illuminate\Support\Facades\Schema::hasTable('cms_templates')) {
        try {
            \Westlinks\Wlcms\Services\TemplateManager::persistRegisteredTemplates();
        } catch (\Exception $e) {
            // Silent fail if any other error occurs
        }
    }
}
```

## Benefits
1. **Prevents unnecessary query execution** when table doesn't exist
2. **Eliminates log noise** on applications without CMS tables
3. **Cleaner error handling** - check before query vs catch after failure
4. **No debug logging** - truly silent when CMS not installed

## Testing
After fix, verify:
- No more "Template registration skipped" messages in production logs
- CMS functionality still works on applications with CMS tables installed
