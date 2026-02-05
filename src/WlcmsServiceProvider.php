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
}