<?php

namespace Westlinks\Wlcms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Westlinks\Wlcms\View\Components\AdminLayout;

class WlcmsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/wlcms.php', 'wlcms');
        $this->mergeConfigFrom(__DIR__.'/../config/navigation.php', 'wlcms.navigation');

        // Register package services
        $this->app->singleton('wlcms', function () {
            return new WlcmsManager();
        });

        // Register Phase 4 services
        $this->app->singleton(\Westlinks\Wlcms\Services\LegacyDatabaseService::class);
        $this->app->singleton(\Westlinks\Wlcms\Services\FieldTransformationService::class);
        $this->app->singleton(\Westlinks\Wlcms\Services\DataValidationService::class);
        $this->app->singleton(\Westlinks\Wlcms\Services\MigrationProgressService::class);
        
        $this->app->bind(\Westlinks\Wlcms\Services\DataMigrationService::class, function ($app) {
            return new \Westlinks\Wlcms\Services\DataMigrationService(
                $app->make(\Westlinks\Wlcms\Services\LegacyDatabaseService::class),
                $app->make(\Westlinks\Wlcms\Services\FieldTransformationService::class)
            );
        });

        // Register Template System services
        $this->app->singleton(\Westlinks\Wlcms\Services\ZoneProcessor::class);
        $this->app->singleton(\Westlinks\Wlcms\Services\TemplateRenderer::class);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/wlcms.php' => config_path('wlcms.php'),
        ], 'wlcms-config');

        // Publish navigation configuration
        $this->publishes([
            __DIR__.'/../config/navigation.php' => config_path('wlcms-navigation.php'),
        ], 'wlcms-navigation');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'wlcms-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/wlcms'),
        ], 'wlcms-views');

        // Publish raw assets for consumer to build
        $this->publishes([
            __DIR__.'/../resources' => resource_path('vendor/wlcms'),
        ], 'wlcms-assets');

        // Publish public assets (images, etc.)
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/wlcms'),
        ], 'wlcms-public');

        // Publish package configuration for consumer's build process  
        $this->publishes([
            __DIR__.'/../package.json' => base_path('vendor/westlinks/wlcms-package.json'),
            __DIR__.'/../vite.config.js' => base_path('vendor/westlinks/wlcms-vite.config.js'),
        ], 'wlcms-build-config');

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wlcms');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register Blade components conditionally to avoid conflicts
        $layoutMode = config('wlcms.layout.mode', 'standalone');
        
        if ($layoutMode === 'standalone') {
            // Only register layout components and namespace in standalone mode
            // Register as 'admin-layout' to match view usage - host app components take precedence if they exist
            Blade::component('admin-layout', AdminLayout::class);
            Blade::componentNamespace('Westlinks\\Wlcms\\View\\Components', 'wlcms');
        }
        // In embedded mode, NO component registration at all - host's components take precedence

        // Register default templates
        $this->registerTemplates();

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCommand::class,
                Commands\MigrateContentCommand::class,
                Commands\MigrateLegacyContentCommand::class,
                Commands\RegenerateThumbnailsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['wlcms'];
    }

    /**
     * Register default templates.
     */
    protected function registerTemplates(): void
    {
        // Full Width Template
        \Westlinks\Wlcms\Services\TemplateManager::register('full-width', [
            'name' => 'Full Width',
            'description' => 'Standard full-width content page with no sidebar. Perfect for About, FAQ, and informational pages.',
            'view' => 'wlcms::templates.full-width',
            'preview' => null,
            'category' => 'content',
            'zones' => [
                'content' => [
                    'label' => 'Main Content',
                    'type' => 'rich_text',
                    'required' => true,
                ],
            ],
            'features' => [],
            'settings_schema' => [
                'show_featured_image' => [
                    'type' => 'select',
                    'label' => 'Show Featured Image',
                    'options' => ['yes' => 'Yes', 'no' => 'No'],
                    'default' => 'no',
                ],
                'featured_image' => [
                    'type' => 'media',
                    'label' => 'Featured Image',
                ],
            ],
        ]);

        // Sidebar Right Template
        \Westlinks\Wlcms\Services\TemplateManager::register('sidebar-right', [
            'name' => 'Sidebar Right',
            'description' => 'Content page with right sidebar for supplementary information, quick links, and resources.',
            'view' => 'wlcms::templates.sidebar-right',
            'preview' => null,
            'category' => 'content',
            'zones' => [
                'content' => [
                    'label' => 'Main Content',
                    'type' => 'rich_text',
                    'required' => true,
                ],
                'sidebar' => [
                    'label' => 'Sidebar Content',
                    'type' => 'rich_text',
                    'required' => false,
                ],
            ],
            'features' => [],
            'settings_schema' => [],
        ]);

        // Contact Form Template
        \Westlinks\Wlcms\Services\TemplateManager::register('contact-form', [
            'name' => 'Contact Form',
            'description' => 'Contact page with embedded form and contact information sidebar.',
            'view' => 'wlcms::templates.contact-form',
            'preview' => null,
            'category' => 'form',
            'zones' => [
                'intro' => [
                    'label' => 'Introduction Text',
                    'type' => 'rich_text',
                    'required' => false,
                ],
                'form' => [
                    'label' => 'Contact Form',
                    'type' => 'form_embed',
                    'required' => true,
                ],
                'contact_info' => [
                    'label' => 'Contact Information',
                    'type' => 'rich_text',
                    'required' => false,
                ],
            ],
            'features' => [
                'form_embed' => true,
            ],
            'settings_schema' => [
                'success_message' => [
                    'type' => 'text',
                    'label' => 'Success Message',
                    'default' => 'Thank you for contacting us!',
                ],
                'redirect_url' => [
                    'type' => 'text',
                    'label' => 'Redirect URL (optional)',
                ],
            ],
        ]);

        // Event Landing Page Template
        \Westlinks\Wlcms\Services\TemplateManager::register('event-landing-page', [
            'name' => 'Event Landing Page',
            'description' => 'Dynamic landing page with hero section, seasonal content zones, and event highlights.',
            'view' => 'wlcms::templates.event-landing-page',
            'preview' => null,
            'category' => 'landing',
            'zones' => [
                'hero' => [
                    'label' => 'Hero Section',
                    'type' => 'rich_text',
                    'required' => false,
                ],
                'seasonal_banner' => [
                    'label' => 'Seasonal Banner',
                    'type' => 'conditional',
                    'required' => false,
                ],
                'content' => [
                    'label' => 'Main Content',
                    'type' => 'rich_text',
                    'required' => true,
                ],
                'features' => [
                    'label' => 'Feature Highlights',
                    'type' => 'repeater',
                    'required' => false,
                ],
                'sponsors' => [
                    'label' => 'Sponsor Logos',
                    'type' => 'media_gallery',
                    'required' => false,
                ],
            ],
            'features' => [
                'seasonal_content' => true,
                'form_embed' => true,
            ],
            'settings_schema' => [
                'campaign_status' => [
                    'type' => 'select',
                    'label' => 'Campaign Status',
                    'options' => [
                        'pre-registration' => 'Pre-Registration',
                        'active' => 'Active/Open',
                        'closed' => 'Closed',
                        'post-event' => 'Post-Event',
                    ],
                    'default' => 'pre-registration',
                ],
                'hero_background' => [
                    'type' => 'media',
                    'label' => 'Hero Background Image',
                ],
                'cta_button_text' => [
                    'type' => 'text',
                    'label' => 'CTA Button Text',
                    'default' => 'Register Now',
                ],
                'cta_button_link' => [
                    'type' => 'text',
                    'label' => 'CTA Button Link',
                    'default' => '#',
                ],
            ],
        ]);

        // Persist registered templates to database
        if (config('wlcms.templates.auto_persist', true)) {
            try {
                \Westlinks\Wlcms\Services\TemplateManager::persistRegisteredTemplates();
            } catch (\Exception $e) {
                // Silent fail during initial installation when tables don't exist yet
                \Illuminate\Support\Facades\Log::debug('Template registration skipped: ' . $e->getMessage());
            }
        }
    }
}
