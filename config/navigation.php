<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Navigation Integration
    |--------------------------------------------------------------------------
    |
    | This configuration defines navigation items that host Laravel applications
    | can integrate into their existing admin interfaces. Each item includes
    | the route, label, icon, and any additional metadata needed for integration.
    |
    */
    
    'items' => [
        [
            'label' => 'CMS Dashboard',
            'route' => 'wlcms.admin.dashboard',
            'icon' => 'squares-2x2',
            'badge' => null,
            'permission' => 'wlcms.view_dashboard',
            'description' => 'Overview of content and media statistics'
        ],
        [
            'label' => 'Content',
            'route' => 'wlcms.admin.content.index',
            'icon' => 'document-text',
            'badge' => null,
            'permission' => 'wlcms.manage_content',
            'description' => 'Create and manage content items',
            'children' => [
                [
                    'label' => 'All Content',
                    'route' => 'wlcms.admin.content.index',
                    'icon' => 'document-duplicate',
                    'permission' => 'wlcms.view_content'
                ],
                [
                    'label' => 'Add New',
                    'route' => 'wlcms.admin.content.create',
                    'icon' => 'plus-circle',
                    'permission' => 'wlcms.create_content'
                ]
            ]
        ],
        [
            'label' => 'Media Library',
            'route' => 'wlcms.admin.media.index',
            'icon' => 'photo',
            'badge' => null,
            'permission' => 'wlcms.manage_media',
            'description' => 'Upload and organize media files'
        ],
        
        // Forms & Submissions
        [
            'label' => 'Forms',
            'route' => 'wlcms.admin.form-submissions.index',
            'icon' => 'document-check',
            'badge' => null,
            'permission' => 'wlcms.manage_forms',
            'description' => 'Manage form submissions and configure forms',
            'children' => [
                [
                    'label' => 'Form Submissions',
                    'route' => 'wlcms.admin.form-submissions.index',
                    'icon' => 'inbox',
                    'permission' => 'wlcms.manage_forms'
                ],
                [
                    'label' => 'Form Configuration',
                    'route' => 'wlcms.admin.forms.index',
                    'icon' => 'cog-6-tooth',
                    'permission' => 'wlcms.manage_forms'
                ]
            ]
        ],
        
        // Legacy Integration (conditionally shown based on config)
        [
            'label' => 'Legacy Integration',
            'route' => 'wlcms.admin.legacy.index',
            'icon' => 'arrow-path-rounded-square',
            'badge' => null,
            'permission' => 'wlcms.manage_legacy',
            'description' => 'Legacy article integration and migration tools',
            'condition' => 'config("wlcms.legacy.enabled", false)', // Only show if legacy is enabled
            'children' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'wlcms.admin.legacy.index',
                    'icon' => 'squares-2x2',
                    'permission' => 'wlcms.manage_legacy'
                ],
                [
                    'label' => 'Article Mappings',
                    'route' => 'wlcms.admin.legacy.mappings.index',
                    'icon' => 'link',
                    'permission' => 'wlcms.manage_legacy'
                ],
                [
                    'label' => 'Navigation Items',
                    'route' => 'wlcms.admin.legacy.navigation.index',
                    'icon' => 'bars-3',
                    'permission' => 'wlcms.manage_legacy'
                ],
                [
                    'label' => 'Migration Tools',
                    'route' => 'wlcms.admin.legacy.migration.index',
                    'icon' => 'arrow-path',
                    'permission' => 'wlcms.manage_legacy'
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Icon Configuration
    |--------------------------------------------------------------------------
    |
    | Define the icon system used by your host application. WLCMS provides
    | mappings for common icon libraries. Customize this based on your
    | admin interface requirements.
    |
    */
    
    'icons' => [
        'type' => env('WLCMS_ICON_TYPE', 'heroicons'), // heroicons, fontawesome, lucide
        'class_prefix' => env('WLCMS_ICON_PREFIX', 'heroicon-o-'),
        
        // Icon mappings for different libraries
        'mappings' => [
            'heroicons' => [
                'squares-2x2' => 'squares-2x2',
                'document-text' => 'document-text',
                'document-duplicate' => 'document-duplicate',
                'plus-circle' => 'plus-circle',
                'photo' => 'photo',
                'folder' => 'folder',
                'cog-6-tooth' => 'cog-6-tooth',
                'arrow-path-rounded-square' => 'arrow-path-rounded-square',
                'link' => 'link',
                'bars-3' => 'bars-3',
                'arrow-path' => 'arrow-path'
            ],
            'fontawesome' => [
                'squares-2x2' => 'fas fa-th',
                'document-text' => 'fas fa-file-alt',
                'document-duplicate' => 'fas fa-copy',
                'plus-circle' => 'fas fa-plus-circle',
                'photo' => 'fas fa-image',
                'folder' => 'fas fa-folder',
                'cog-6-tooth' => 'fas fa-cog'
            ],
            'lucide' => [
                'squares-2x2' => 'grid',
                'document-text' => 'file-text',
                'document-duplicate' => 'copy',
                'plus-circle' => 'plus-circle',
                'photo' => 'image',
                'folder' => 'folder',
                'cog-6-tooth' => 'settings'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Define the permissions used by WLCMS navigation items. Host applications
    | can map these to their existing permission systems or use them directly
    | with Spatie Laravel Permission package.
    |
    */
    
    'permissions' => [
        'wlcms.view_dashboard' => 'View CMS Dashboard',
        'wlcms.manage_content' => 'Manage Content',
        'wlcms.view_content' => 'View Content',
        'wlcms.create_content' => 'Create Content',
        'wlcms.edit_content' => 'Edit Content',
        'wlcms.delete_content' => 'Delete Content',
        'wlcms.manage_media' => 'Manage Media Library',
        'wlcms.upload_media' => 'Upload Media Files',
        'wlcms.delete_media' => 'Delete Media Files',
        
        // Legacy Integration Permissions
        'wlcms.manage_legacy' => 'Manage Legacy Integration',
        'wlcms.view_legacy_mappings' => 'View Legacy Article Mappings',
        'wlcms.create_legacy_mappings' => 'Create Legacy Article Mappings',
        'wlcms.edit_legacy_mappings' => 'Edit Legacy Article Mappings',
        'wlcms.delete_legacy_mappings' => 'Delete Legacy Article Mappings',
        'wlcms.sync_legacy_mappings' => 'Sync Legacy Article Mappings',
        'wlcms.manage_legacy_navigation' => 'Manage Legacy Navigation Items',
        'wlcms.use_legacy_migration_tools' => 'Use Legacy Migration Tools'
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Helpers
    |--------------------------------------------------------------------------
    |
    | Helper methods and configurations for common admin interface integrations.
    |
    */
    
    'integrations' => [
        
        // Laravel Nova integration
        'nova' => [
            'enabled' => false,
            'group' => 'Content Management',
            'icon' => 'collection'
        ],
        
        // Filament integration
        'filament' => [
            'enabled' => false,
            'group' => 'Content',
            'sort' => 100
        ],
        
        // Custom admin panel integration
        'custom' => [
            'enabled' => true,
            'group' => 'CMS',
            'wrapper_class' => 'nav-item'
        ]
    ]
];