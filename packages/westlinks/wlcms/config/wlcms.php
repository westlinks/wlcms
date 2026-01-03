<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WLCMS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the WLCMS package.
    | You can publish this config file with:
    | php artisan vendor:publish --tag=wlcms-config
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Admin Interface
    |--------------------------------------------------------------------------
    */
    'admin' => [
        // Admin route prefix (e.g., /admin/cms or /dashboard/content)
        'prefix' => 'admin/cms',

        // Middleware for admin routes
        'middleware' => ['web', 'auth'],

        // Admin layout view
        'layout' => 'layouts.admin',

        // Items per page in admin listings
        'per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Settings
    |--------------------------------------------------------------------------
    */
    'content' => [
        // Default template for new content items
        'default_template' => 'default',

        // Available templates
        'templates' => [
            'default' => 'Default Page',
            'full-width' => 'Full Width',
            'narrow-right' => 'Narrow Right Sidebar',
        ],

        // Auto-save draft interval (seconds)
        'autosave_interval' => 30,

        // Number of revisions to keep
        'max_revisions' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    */
    'media' => [
        // Storage disk for media files
        'disk' => 'public',

        // Upload path within the disk
        'path' => 'cms/media',

        // Maximum file size in MB
        'max_file_size' => 10,

        // Allowed file types
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'archive' => ['zip', 'rar', '7z'],
            'audio' => ['mp3', 'wav', 'ogg'],
            'video' => ['mp4', 'avi', 'mov', 'wmv'],
        ],

        // Image thumbnail sizes
        'thumbnails' => [
            'small' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 300, 'height' => 300],
            'large' => ['width' => 800, 'height' => 600],
        ],

        // Image optimization settings
        'optimize' => [
            'quality' => 85,
            'auto_orient' => true,
            'strip_meta' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    */
    'seo' => [
        // Automatically generate sitemap
        'auto_sitemap' => true,

        // Sitemap route
        'sitemap_route' => 'sitemap.xml',

        // Default meta description length
        'meta_description_length' => 160,

        // Open Graph image dimensions
        'og_image' => [
            'width' => 1200,
            'height' => 630,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model Integration
    |--------------------------------------------------------------------------
    |
    | Configure how the package integrates with your User model.
    | The package adapts to different User model schemas.
    |
    */
    'user' => [
        // User model class (set to null to disable user integration)
        'model' => App\Models\User::class,

        // Primary key field (usually 'id')
        'primary_key' => 'id',

        // How to display the user's name - choose one approach:
        
        // Option 1: Combine multiple fields (default for West Links applications)
        'display_name' => [
            'type' => 'fields',
            'fields' => ['firstname', 'lastname'], // Standard West Links schema
            'separator' => ' ',
        ],
        
        // Option 2: Single field name
        // 'display_name' => [
        //     'type' => 'field',
        //     'field' => 'name', // 'name', 'username', 'display_name', etc.
        // ],
        
        // Option 3: Use a model accessor/method
        // 'display_name' => [
        //     'type' => 'method',
        //     'method' => 'getFullNameAttribute', // or 'full_name' accessor
        // ],
        
        // Option 4: Custom format string
        // 'display_name' => [
        //     'type' => 'format',
        //     'format' => '{firstname} {lastname}', // Available: {fieldName}
        // ],

        // User avatar field (optional) - field name or null
        'avatar_field' => null, // 'avatar', 'profile_photo_path', etc.
        
        // User email field (for admin notifications, etc.)
        'email_field' => 'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        // Enable Spatie Laravel Permission integration
        'enabled' => true,

        // Permission names
        'names' => [
            'content.view' => 'View Content',
            'content.create' => 'Create Content',
            'content.edit' => 'Edit Content',
            'content.delete' => 'Delete Content',
            'content.publish' => 'Publish Content',
            'media.view' => 'View Media',
            'media.upload' => 'Upload Media',
            'media.edit' => 'Edit Media',
            'media.delete' => 'Delete Media',
        ],
    ],
];