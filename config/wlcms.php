<?php

return [

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
        
        // User name field for attribution
        'name_field' => 'name',
        
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
        'disk' => 'public',
        
        // Base path for media uploads
        'base_path' => 'cms/media',
        
        // Maximum file size in KB
        'max_file_size' => 10240, // 10MB
        
        // Allowed file types
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'video' => ['mp4', 'mov', 'avi', 'wmv', 'flv'],
            'audio' => ['mp3', 'wav', 'ogg', 'aac'],
        ],
        
        // Image processing settings
        'image' => [
            'quality' => 85,
            'thumbnails' => [
                'small' => [150, 150],
                'medium' => [300, 300], 
                'large' => [800, 600],
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

];