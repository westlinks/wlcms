<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Layout Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how WLCMS integrates with your application's admin interface.
    | 'standalone' mode provides complete admin interface with navigation.
    | 'embedded' mode provides content-only views for integration with existing admins.
    |
    */
    'layout' => [
        // Layout mode: 'standalone' or 'embedded'
        'mode' => env('WLCMS_LAYOUT_MODE', 'standalone'),
        
        // Host layout component for embedded mode (default matches standard Laravel admin layout)
        'host_layout' => env('WLCMS_HOST_LAYOUT', 'layouts.admin-layout'),
        
        // Enable navigation integration for host apps
        'navigation_integration' => env('WLCMS_NAVIGATION_INTEGRATION', false),
        
        // Route prefix for embedded mode (helps avoid conflicts)
        'embedded_prefix' => env('WLCMS_EMBEDDED_PREFIX', 'cms'),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model Integration
    |--------------------------------------------------------------------------
    |
    | This section configures how WLCMS integrates with your application's
    | user model. Set to null to disable user integration completely.
    |
    */
    'user' => [
        // User model class - set to null to disable user integration
        'model' => null, // Example: \App\Models\User::class
        
        // Foreign key column name in WLCMS tables
        'foreign_key' => 'user_id',
        
        // Primary key column name in user table
        'primary_key' => 'id',
        
        // User name field for attribution (can be a field name or accessor)
        // For firstname + lastname, use 'full_name' and add accessor to your User model:
        // public function getFullNameAttribute() { return $this->firstname . ' ' . $this->lastname; }
        'name_field' => 'name', // Examples: 'name', 'full_name', 'display_name'
        
        // User email field for attribution  
        'email_field' => 'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for content management features
    |
    */
    'content' => [
        // Maximum content revisions to keep (0 = unlimited)
        'max_revisions' => 10,
        
        // Auto-save draft interval in minutes
        'auto_save_interval' => 2,
        
        // Default content status
        'default_status' => 'draft',
        
        // Allowed content statuses
        'statuses' => [
            'draft',
            'published', 
            'scheduled',
            'archived',
        ],
        
        // Content types
        'types' => [
            'page',
            'post', 
            'article',
            'news',
            'event',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Settings  
    |--------------------------------------------------------------------------
    |
    | Configuration for media management
    |
    */
    'media' => [
        // Storage disk for media files
        'disk' => env('WLCMS_STORAGE_DISK', 'public'),
        
        // Base path for media uploads
        'base_path' => 'wlcms',
        
        // Maximum file size in KB (20MB)
        'max_file_size' => env('WLCMS_MAX_FILE_SIZE', 20480),
        
        // Allowed file types
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf'],
            'video' => ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm'],
            'audio' => ['mp3', 'wav', 'ogg', 'aac', 'flac'],
        ],
        
        // Image processing settings
        'image' => [
            'quality' => env('WLCMS_IMAGE_QUALITY', 90), // Increased for better quality
            'generate_thumbnails' => true,
            'extract_exif' => true,
            'thumbnails' => [
                'thumb' => [150, 150],
                'small' => [300, 300],
                'medium' => [600, 600],
                'large' => [1200, 1200],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for SEO features
    |
    */
    'seo' => [
        // Enable SEO features
        'enabled' => true,
        
        // Default meta description length
        'meta_description_length' => 160,
        
        // Default meta title length
        'meta_title_length' => 60,
        
        // Auto-generate meta from content
        'auto_meta' => true,
        
        // Sitemap settings
        'sitemap' => [
            'enabled' => true,
            'cache_duration' => 3600, // 1 hour
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for content caching
    |
    */
    'cache' => [
        // Enable content caching
        'enabled' => true,
        
        // Default cache duration in minutes
        'duration' => 60,
        
        // Cache key prefix
        'prefix' => 'wlcms',
        
        // Cache driver
        'driver' => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for API endpoints
    |
    */
    'api' => [
        // Enable API routes
        'enabled' => true,
        
        // API route prefix
        'prefix' => 'api/cms',
        
        // API middleware
        'middleware' => ['api'],
        
        // Rate limiting
        'rate_limit' => '60,1', // 60 requests per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for security features
    |
    */
    'security' => [
        // Enable permission checking
        'permissions' => true,
        
        // Permission guard
        'guard' => 'web',
        
        // Default permissions
        'default_permissions' => [
            'cms.content.view',
            'cms.content.create', 
            'cms.content.edit',
            'cms.content.delete',
            'cms.media.view',
            'cms.media.upload',
            'cms.media.delete',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for frontend content display
    |
    */
    'frontend' => [
        // Enable frontend routes (disabled by default to avoid conflicts)
        'enabled' => false,
        
        // Route prefix for frontend content
        'prefix' => 'cms-content',
        
        // Middleware for frontend routes
        'middleware' => ['web'],
    ],

];