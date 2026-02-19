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

        // Register Form System services (Phase 8) - Register these first
        $this->app->singleton(\Westlinks\Wlcms\Services\FormRegistry::class);
        $this->app->singleton(\Westlinks\Wlcms\Services\FormRenderer::class);
        $this->app->singleton(\Westlinks\Wlcms\Services\ShortcodeParser::class);
        
        // Register Template System services with dependencies
        $this->app->singleton(\Westlinks\Wlcms\Services\ZoneProcessor::class, function ($app) {
            return new \Westlinks\Wlcms\Services\ZoneProcessor(
                $app->make(\Westlinks\Wlcms\Services\ShortcodeParser::class)
            );
        });
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
        
        // Register default forms
        $this->registerForms();

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCommand::class,
                Commands\MigrateContentCommand::class,
                Commands\MigrateLegacyContentCommand::class,
                Commands\RegenerateThumbnailsCommand::class,
                Commands\CheckContentActivations::class,
                Commands\PublishTemplatesCommand::class,
                Commands\ValidateTemplateCommand::class,
            ]);
        }

        // Register scheduled tasks
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Run content activation check every hour to honor specific activation times
            $schedule->command('wlcms:check-activations')
                ->hourly()
                ->timezone(config('app.timezone', 'UTC'))
                ->onSuccess(function () {
                    \Illuminate\Support\Facades\Log::info('WLCMS: Content activation check completed successfully');
                })
                ->onFailure(function () {
                    \Illuminate\Support\Facades\Log::error('WLCMS: Content activation check failed');
                });
        });
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

        // Simple HTML Block Template
        \Westlinks\Wlcms\Services\TemplateManager::register('simple-html-block', [
            'name' => 'Simple HTML Block',
            'description' => 'Reusable HTML content block for embedding in other pages (similar to WordPress custom HTML blocks).',
            'view' => 'wlcms::templates.simple-html-block',
            'preview' => null,
            'category' => 'content',
            'zones' => [
                'content' => [
                    'label' => 'HTML Content',
                    'type' => 'rich_text',
                    'required' => true,
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
                'content' => [
                    'label' => 'Main Content',
                    'type' => 'rich_text',
                    'required' => true,
                ],
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

        // Event Registration Template - Advanced
        \Westlinks\Wlcms\Services\TemplateManager::register('event-registration', [
            'name' => 'Event Registration',
            'description' => 'Specialized template for event registration with status-based content switching.',
            'view' => 'wlcms::templates.event-registration',
            'preview' => null,
            'category' => 'landing',
            'zones' => [
                'pre_registration' => [
                    'label' => 'Pre-Registration Content',
                    'type' => 'rich_text',
                    'required' => false,
                ],
                'active_event' => [
                    'label' => 'Active Event Content',
                    'type' => 'rich_text',
                    'required' => false,
                ],
                'closed_event' => [
                    'label' => 'Closed/Past Event Content',
                    'type' => 'rich_text',
                    'required' => false,
                ],
            ],
            'settings_schema' => [
                'registration_status' => [
                    'type' => 'select',
                    'label' => 'Registration Status',
                    'options' => [
                        'upcoming' => 'Upcoming (Pre-Registration)',
                        'open' => 'Open (Active)',
                        'closed' => 'Closed',
                    ],
                    'default' => 'upcoming',
                ],
                'event_date' => [
                    'type' => 'date',
                    'label' => 'Event Date',
                ],
                'registration_link' => [
                    'type' => 'text',
                    'label' => 'Registration Form Link',
                ],
            ],
        ]);

        // Signup Form Page - Advanced
        \Westlinks\Wlcms\Services\TemplateManager::register('signup-form-page', [
            'name' => 'Signup Form Page',
            'description' => 'Full-page template optimized for signup forms with minimal distractions.',
            'view' => 'wlcms::templates.signup-form-page',
            'preview' => null,
            'category' => 'landing',
            'zones' => [
                'header' => [
                    'label' => 'Header Content',
                    'type' => 'rich_text',
                    'required' => false,
                ],
                'form' => [
                    'label' => 'Form Area',
                    'type' => 'form_embed',
                    'required' => true,
                ],
                'footer' => [
                    'label' => 'Footer/Fine Print',
                    'type' => 'rich_text',
                    'required' => false,
                ],
            ],
            'settings_schema' => [
                'background_color' => [
                    'type' => 'color',
                    'label' => 'Background Color',
                    'default' => '#ffffff',
                ],
                'show_logo' => [
                    'type' => 'toggle',
                    'label' => 'Show Logo',
                    'default' => true,
                ],
            ],
        ]);

        // Time-Limited Content Template - Advanced
        \Westlinks\Wlcms\Services\TemplateManager::register('time-limited-content', [
            'name' => 'Time-Limited Content',
            'description' => 'Content with specific availability dates and downloadable files.',
            'view' => 'wlcms::templates.time-limited-content',
            'preview' => null,
            'category' => 'content',
            'zones' => [
                'content' => [
                    'label' => 'Main Content',
                    'type' => 'rich_text',
                    'required' => true,
                ],
                'files' => [
                    'label' => 'Downloadable Files',
                    'type' => 'file_list',
                    'required' => false,
                ],
            ],
            'settings_schema' => [
                'available_from' => [
                    'type' => 'date',
                    'label' => 'Available From',
                ],
                'available_until' => [
                    'type' => 'date',
                    'label' => 'Available Until',
                ],
                'expiration_message' => [
                    'type' => 'text',
                    'label' => 'Expiration Message',
                    'default' => 'This content is no longer available.',
                ],
            ],
        ]);

        // Archive Timeline Template - Advanced
        \Westlinks\Wlcms\Services\TemplateManager::register('archive-timeline', [
            'name' => 'Archive Timeline',
            'description' => 'Year-based timeline with photo galleries and historical content.',
            'view' => 'wlcms::templates.archive-timeline',
            'preview' => null,
            'category' => 'content',
            'zones' => [
                'intro' => [
                    'label' => 'Introduction',
                    'type' => 'rich_text',
                    'required' => false,
                ],
                'timeline_items' => [
                    'label' => 'Timeline Entries',
                    'type' => 'repeater',
                    'required' => true,
                ],
                'gallery' => [
                    'label' => 'Photo Gallery',
                    'type' => 'media_gallery',
                    'required' => false,
                ],
            ],
            'settings_schema' => [
                'default_year' => [
                    'type' => 'number',
                    'label' => 'Default Year to Display',
                    'default' => date('Y'),
                ],
                'show_year_selector' => [
                    'type' => 'toggle',
                    'label' => 'Show Year Selector',
                    'default' => true,
                ],
            ],
        ]);

        // Register custom templates from host application config
        if (config('wlcms.templates.allow_custom', true)) {
            $customTemplates = config('wlcms.templates.custom', []);
            
            foreach ($customTemplates as $identifier => $config) {
                \Westlinks\Wlcms\Services\TemplateManager::register($identifier, $config);
            }
        }

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
    }

    /**
     * Register default forms.
     */
    protected function registerForms(): void
    {
        $formRegistry = $this->app->make(\Westlinks\Wlcms\Services\FormRegistry::class);

        // Contact Form
        $formRegistry->register('contact', [
            'name' => 'Contact Form',
            'type' => 'built-in',
            'description' => 'Basic contact form with name, email, and message fields',
            'view' => 'wlcms::forms.contact',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'text',
                    'label' => 'Your Name',
                    'required' => true,
                    'placeholder' => 'Your Name',
                ],
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => 'Email Address',
                    'required' => true,
                    'placeholder' => 'email@example.com',
                ],
                [
                    'name' => 'phone',
                    'type' => 'tel',
                    'label' => 'Phone Number',
                    'required' => false,
                    'placeholder' => '(555) 123-4567',
                ],
                [
                    'name' => 'subject',
                    'type' => 'text',
                    'label' => 'Subject',
                    'required' => false,
                    'placeholder' => 'How can we help?',
                ],
                [
                    'name' => 'message',
                    'type' => 'textarea',
                    'label' => 'Message',
                    'required' => true,
                    'placeholder' => 'Tell us more...',
                    'rows' => 5,
                ],
            ],
            'validation' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'subject' => 'nullable|string|max:255',
                'message' => 'required|string|max:5000',
            ],
            'settings' => [
                'notify_email' => config('mail.from.address', 'admin@example.com'),
                'email_subject' => 'New Contact Form Submission',
            ],
            'success_message' => 'Thank you for contacting us! We will get back to you soon.',
            'redirect_url' => '/wlcms/forms/contact/thank-you',
            'thank_you_title' => 'Thank You for Contacting Us!',
            'thank_you_content' => '<p class="text-lg mb-4">We have received your message and appreciate you reaching out.</p><p>Our team will review your inquiry and get back to you within 24-48 hours.</p>',
        ]);

        // Newsletter Signup Form
        $formRegistry->register('newsletter', [
            'name' => 'Newsletter Signup',
            'type' => 'built-in',
            'description' => 'Simple newsletter subscription form',
            'view' => 'wlcms::forms.newsletter',
            'fields' => [
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => 'Email Address',
                    'required' => true,
                    'placeholder' => 'your@email.com',
                ],
                [
                    'name' => 'consent',
                    'type' => 'checkbox',
                    'label' => 'I agree to receive newsletters',
                    'required' => true,
                ],
            ],
            'validation' => [
                'email' => 'required|email|max:255',
                'consent' => 'required|accepted',
            ],
            'settings' => [
                'notify_email' => config('mail.from.address', 'admin@example.com'),
                'email_subject' => 'New Newsletter Subscription',
            ],
            'success_message' => 'Thank you for subscribing to our newsletter!',
            'redirect_url' => '/wlcms/forms/newsletter/thank-you',
            'thank_you_title' => 'Welcome to Our Newsletter!',
            'thank_you_content' => '<p class="text-lg mb-4">Thank you for subscribing!</p><p>You\'ll receive our latest updates, news, and exclusive content directly in your inbox.</p>',
        ]);

        // Feedback Form
        $formRegistry->register('feedback', [
            'name' => 'Feedback Form',
            'type' => 'built-in',
            'description' => 'Customer feedback and rating form',
            'view' => 'wlcms::forms.feedback',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'text',
                    'label' => 'Your Name',
                    'required' => false,
                    'placeholder' => 'Anonymous',
                ],
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => 'Email Address',
                    'required' => false,
                    'placeholder' => 'your@email.com',
                ],
                [
                    'name' => 'rating',
                    'type' => 'select',
                    'label' => 'Overall Rating',
                    'required' => true,
                    'options' => [
                        '5' => '5 - Excellent',
                        '4' => '4 - Good',
                        '3' => '3 - Average',
                        '2' => '2 - Poor',
                        '1' => '1 - Very Poor',
                    ],
                ],
                [
                    'name' => 'feedback',
                    'type' => 'textarea',
                    'label' => 'Your Feedback',
                    'required' => true,
                    'placeholder' => 'Tell us what you think...',
                    'rows' => 5,
                ],
            ],
            'validation' => [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'rating' => 'required|integer|between:1,5',
                'feedback' => 'required|string|max:5000',
            ],
            'settings' => [
                'notify_email' => config('mail.from.address', 'admin@example.com'),
                'email_subject' => 'New Feedback Submission',
            ],
            'success_message' => 'Thank you for your feedback!',
            'redirect_url' => '/wlcms/forms/feedback/thank-you',
            'thank_you_title' => 'Thank You for Your Feedback!',
            'thank_you_content' => '<p class="text-lg mb-4">Your feedback is invaluable to us.</p><p>We carefully review all feedback to improve our services and better serve you.</p>',
        ]);
    }
}

