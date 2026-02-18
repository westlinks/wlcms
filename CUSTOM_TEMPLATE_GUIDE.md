# WLCMS Custom Template Development Guide

**Version:** 1.0  
**Last Updated:** February 17, 2026  
**Audience:** Developers extending WLCMS with custom templates

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Publishing Templates](#publishing-templates)
3. [Creating Custom Templates](#creating-custom-templates)
4. [Template Configuration](#template-configuration)
5. [Zone Types](#zone-types)
6. [Settings Schema](#settings-schema)
7. [Registering Custom Templates](#registering-custom-templates)
8. [Validation](#validation)
9. [Best Practices](#best-practices)
10. [Examples](#examples)

---

## Quick Start

### 5-Minute Custom Template

```bash
# 1. Publish templates
php artisan wlcms:publish-templates

# 2. Copy a template to customize
cp resources/views/vendor/wlcms/templates/full-width.blade.php \\
   resources/views/vendor/wlcms/templates/my-template.blade.php

# 3. Register in config/wlcms.php
'templates' => [
    'custom' => [
        'my-template' => [
            'name' => 'My Custom Template',
            'view' => 'vendor.wlcms.templates.my-template',
            // ... other config
        ],
    ],
],

# 4. Validate
php artisan wlcms:validate-template my-template --path=vendor.wlcms.templates.my-template

# 5. Clear cache
php artisan optimize:clear
```

---

## Publishing Templates

### Publish All Templates

```bash
php artisan wlcms:publish-templates
```

Templates are published to: `resources/views/vendor/wlcms/templates/`

### Publish Specific Template

```bash
php artisan wlcms:publish-templates --template=full-width
```

### Force Overwrite

```bash
php artisan wlcms:publish-templates --force
```

### Published Files

```
resources/views/vendor/wlcms/templates/
├── full-width.blade.php
├── sidebar-right.blade.php
├── contact-form.blade.php
├── event-landing-page.blade.php
├── event-registration.blade.php
├── signup-form-page.blade.php
├── time-limited-content.blade.php
└── archive-timeline.blade.php
```

---

## Creating Custom Templates

### Step 1: Create Blade Template

Create a new Blade file in `resources/views/vendor/wlcms/templates/`:

```blade
{{-- resources/views/vendor/wlcms/templates/portfolio.blade.php --}}

<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'" :content="$content">
    @push('styles')
    <style>
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        .portfolio-item {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
    @endpush

    <div class="portfolio-template">
        {{-- Introduction --}}
        @if(!empty($zones['intro']))
            <div class="intro-section">
                {!! $zones['intro'] !!}
            </div>
        @endif

        {{-- Portfolio Grid --}}
        @if(!empty($zones['projects']))
            <div class="portfolio-grid">
                @foreach($zones['projects'] as $project)
                    <div class="portfolio-item">
                        @if(!empty($project['image']))
                            <img src="{{ $project['image'] }}" alt="{{ $project['title'] }}">
                        @endif
                        <div class="p-4">
                            <h3>{{ $project['title'] }}</h3>
                            <p>{{ $project['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Contact CTA --}}
        @if(!empty($settings['show_cta']) && $settings['show_cta'] === 'yes')
            <div class="cta-section">
                <a href="{{ $settings['cta_link'] ?? '#' }}" class="btn-primary">
                    {{ $settings['cta_text'] ?? 'Get in Touch' }}
                </a>
            </div>
        @endif
    </div>
</x-dynamic-component>
```

### Step 2: Register Template Configuration

Add to `config/wlcms.php`:

```php
'templates' => [
    'custom' => [
        'portfolio' => [
            'name' => 'Portfolio',
            'description' => 'Showcase projects in a responsive grid layout',
            'view' => 'vendor.wlcms.templates.portfolio',
            'preview' => '/images/templates/portfolio-preview.png',
            'category' => 'custom',
            'zones' => [
                'intro' => [
                    'label' => 'Introduction Text',
                    'type' => 'rich_text',
                    'required' => false,
                ],
                'projects' => [
                    'label' => 'Portfolio Projects',
                    'type' => 'repeater',
                    'required' => true,
                ],
            ],
            'features' => [],
            'settings_schema' => [
                'show_cta' => [
                    'type' => 'select',
                    'label' => 'Show Call-to-Action',
                    'options' => ['yes' => 'Yes', 'no' => 'No'],
                    'default' => 'yes',
                ],
                'cta_text' => [
                    'type' => 'text',
                    'label' => 'CTA Button Text',
                    'default' => 'Get in Touch',
                ],
                'cta_link' => [
                    'type' => 'text',
                    'label' => 'CTA Button Link',
                    'default' => '/contact',
                ],
            ],
        ],
    ],
],
```

### Step 3: Validate

```bash
php artisan wlcms:validate-template portfolio --path=vendor.wlcms.templates.portfolio
```

### Step 4: Clear Cache

```bash
php artisan optimize:clear
```

---

## Template Configuration

### Required Fields

```php
[
    'name' => 'Template Display Name',     // REQUIRED
    'view' => 'path.to.blade.template',    // REQUIRED
    'zones' => [],                         // REQUIRED (can be empty)
]
```

### Optional Fields

```php
[
    'description' => 'Describe what the template is for',
    'preview' => '/images/preview.png',  // Preview image path
    'category' => 'content',             // Category: content, form, landing, custom
    'features' => [],                    // Feature flags
    'settings_schema' => [],             // Template-specific settings
]
```

### Full Example

```php
'my-template' => [
    'name' => 'My Custom Template',
    'description' => 'A custom template for special content pages',
    'view' => 'vendor.wlcms.templates.my-template',
    'preview' => '/images/templates/my-template.png',
    'category' => 'custom',
    'zones' => [
        'content' => [
            'label' => 'Main Content',
            'type' => 'rich_text',
            'required' => true,
        ],
    ],
    'features' => [
        'form_embed' => false,
        'seasonal_content' => false,
    ],
    'settings_schema' => [
        'layout_style' => [
            'type' => 'select',
            'label' => 'Layout Style',
            'options' => [
                'boxed' => 'Boxed',
                'full-width' => 'Full Width',
            ],
            'default' => 'boxed',
        ],
    ],
],
```

---

## Zone Types

### 1. Rich Text (`rich_text`)

WYSIWYG editor for formatted content.

```php
'content' => [
    'label' => 'Main Content',
    'type' => 'rich_text',
    'required' => true,
],
```

**Blade Usage:**
```blade
{!! $zones['content'] !!}
```

---

### 2. Form Embed (`form_embed`)

Embed forms via shortcode or form ID.

```php
'form' => [
    'label' => 'Embedded Form',
    'type' => 'form_embed',
    'required' => false,
],
```

**Blade Usage:**
```blade
{!! $zones['form'] !!}
```

---

### 3. Repeater (`repeater`)

Multiple instances of structured data.

```php
'features' => [
    'label' => 'Feature List',
    'type' => 'repeater',
    'required' => false,
],
```

**Blade Usage:**
```blade
@foreach($zones['features'] as $feature)
    <div class="feature">
        <h3>{{ $feature['title'] }}</h3>
        <p>{{ $feature['description'] }}</p>
    </div>
@endforeach
```

---

### 4. Media Gallery (`media_gallery`)

Image upload and gallery.

```php
'gallery' => [
    'label' => 'Photo Gallery',
    'type' => 'media_gallery',
    'required' => false,
],
```

**Blade Usage:**
```blade
@foreach($zones['gallery'] as $image)
    <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}">
@endforeach
```

---

### 5. File List (`file_list`)

Downloadable files (PDFs, documents).

```php
'downloads' => [
    'label' => 'Download Files',
    'type' => 'file_list',
    'required' => false,
],
```

**Blade Usage:**
```blade
@foreach($zones['downloads'] as $file)
    <a href="{{ $file['url'] }}" download>
        {{ $file['title'] }}
    </a>
@endforeach
```

---

### 6. Link List (`link_list`)

External/internal links.

```php
'links' => [
    'label' => 'Quick Links',
    'type' => 'link_list',
    'required' => false,
],
```

**Blade Usage:**
```blade
@foreach($zones['links'] as $link)
    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
@endforeach
```

---

### 7. Conditional (`conditional`)

Show/hide based on settings.

```php
'banner' => [
    'label' => 'Promotional Banner',
    'type' => 'conditional',
    'required' => false,
],
```

**Blade Usage:**
```blade
@if($settings['show_banner'] === 'yes')
    {!! $zones['banner'] !!}
@endif
```

---

## Settings Schema

### Text Input

```php
'subtitle' => [
    'type' => 'text',
    'label' => 'Page Subtitle',
    'default' => '',
],
```

###Select Dropdown

```php
'layout' => [
    'type' => 'select',
    'label' => 'Layout Style',
    'options' => [
        'boxed' => 'Boxed',
        'wide' => 'Wide',
    ],
    'default' => 'boxed',
],
```

### Toggle/Boolean

```php
'show_breadcrumbs' => [
    'type' => 'toggle',
    'label' => 'Show Breadcrumbs',
    'default' => true,
],
```

### Media Upload

```php
'background_image' => [
    'type' => 'media',
    'label' => 'Background Image',
],
```

### Color Picker

```php
'accent_color' => [
    'type' => 'color',
    'label' => 'Accent Color',
    'default' => '#3b82f6',
],
```

### Date Picker

```php
'event_date' => [
    'type' => 'date',
    'label' => 'Event Date',
],
```

### Number Input

```php
'columns' => [
    'type' => 'number',
    'label' => 'Number of Columns',
    'default' => 3,
],
```

---

## Registering Custom Templates

### Method 1: Config File (Recommended)

Edit `config/wlcms.php`:

```php
'templates' => [
    'custom' => [
        'my-template' => [ /* config */ ],
        'another-template' => [ /* config */ ],
    ],
],
```

### Method 2: Service Provider

Create `App\\Providers\\TemplateServiceProvider.php`:

```php
<?php

namespace App\\Providers;

use Illuminate\\Support\\ServiceProvider;
use Westlinks\\Wlcms\\Services\\TemplateManager;

class TemplateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        TemplateManager::register('my-template', [
            'name' => 'My Template',
            'view' => 'templates.my-template',
            'zones' => [ /* ... */ ],
        ]);
    }
}
```

Register in `config/app.php`:

```php
'providers' => [
    // ...
    App\\Providers\\TemplateServiceProvider::class,
],
```

---

## Validation

### Validate Template

```bash
php artisan wlcms:validate-template {identifier} --path={view.path}
```

### Example

```bash
php artisan wlcms:validate-template portfolio --path=vendor.wlcms.templates.portfolio
```

### What Gets Validated

1. ✓ Template registration in database
2. ✓ View file exists
3. ✓ View compiles without errors
4. ✓ Zone configuration structure
5. ✓ Settings schema structure
6. ✓ Required zones definition

---

## Best Practices

### 1. Use Dynamic Component Layout

Always use `<x-dynamic-component>` for host app integration:

```blade
<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'" :content="$content">
    {{-- Your template content --}}
</x-dynamic-component>
```

### 2. Null-Safe Zone Checks

Always check if zones exist before rendering:

```blade
@if(!empty($zones['content']))
    {!! $zones['content'] !!}
@endif
```

### 3. Default Values for Settings

Provide defaults for all settings:

```blade
{{ $settings['title'] ?? 'Default Title' }}
```

### 4. CSS Scoping

Scope your styles to avoid conflicts:

```blade
@push('styles')
<style>
    .my-template-wrapper {
        /* Your styles */
    }
</style>
@endpush
```

### 5. Semantic HTML

Use semantic HTML5 elements:

```blade
<article class="my-template">
    <header>...</header>
    <main>...</main>
    <footer>...</footer>
</article>
```

### 6. Responsive Design

Make templates mobile-friendly:

```css
.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}
```

### 7. Accessibility

Include ARIA labels and alt text:

```blade
<img src="{{ $image }}" alt="{{ $altText }}" role="img">
<button aria-label="Close" @click="close()">×</button>
```

---

## Examples

### Example 1: Simple Custom Template

**Template:** `resources/views/vendor/wlcms/templates/testimonial.blade.php`

```blade
<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'" :content="$content">
    <div class="testimonial-template max-w-4xl mx-auto py-12">
        @if(!empty($zones['headline']))
            <h1 class="text-4xl font-bold text-center mb-8">
                {!! $zones['headline'] !!}
            </h1>
        @endif

        @if(!empty($zones['testimonials']))
            <div class="space-y-8">
                @foreach($zones['testimonials'] as $testimonial)
                    <blockquote class="testimonial-item bg-gray-50 p-6 rounded-lg">
                        <p class="text-lg italic">"{{ $testimonial['quote'] }}"</p>
                        <footer class="mt-4 text-right">
                            <cite class="font-semibold">— {{ $testimonial['author'] }}</cite>
                            @if(!empty($testimonial['company']))
                                <span class="text-gray-600">, {{ $testimonial['company'] }}</span>
                            @endif
                        </footer>
                    </blockquote>
                @endforeach
            </div>
        @endif
    </div>
</x-dynamic-component>
```

**Config:**

```php
'testimonial' => [
    'name' => 'Testimonials',
    'description' => 'Showcase customer testimonials',
    'view' => 'vendor.wlcms.templates.testimonial',
    'category' => 'custom',
    'zones' => [
        'headline' => [
            'label' => 'Page Headline',
            'type' => 'rich_text',
            'required' => false,
        ],
        'testimonials' => [
            'label' => 'Testimonials',
            'type' => 'repeater',
            'required' => true,
        ],
    ],
    'settings_schema' => [],
],
```

---

### Example 2: Template with Settings

**Template:** `resources/views/vendor/wlcms/templates/pricing.blade.php`

```blade
<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'" :content="$content">
    @push('styles')
    <style>
        .pricing-card {
            border: 2px solid {{ $settings['accent_color'] ?? '#3b82f6' }};
            border-radius: 0.5rem;
            padding: 2rem;
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat({{ $settings['columns'] ?? 3 }}, 1fr);
            gap: 2rem;
        }
    </style>
    @endpush

    <div class="pricing-template">
        <div class="text-center mb-12">
            {!! $zones['intro'] ?? '' !!}
        </div>

        <div class="pricing-grid">
            @foreach($zones['plans'] ?? [] as $plan)
                <div class="pricing-card">
                    <h3>{{ $plan['name'] }}</h3>
                    <div class="price">${{ $plan['price'] }}/mo</div>
                    <ul>
                        @foreach($plan['features'] as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ $plan['signup_link'] }}" class="btn-primary">
                        Sign Up
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
```

**Config:**

```php
'pricing' => [
    'name' => 'Pricing Table',
    'description' => 'Display pricing plans in a grid',
    'view' => 'vendor.wlcms.templates.pricing',
    'category' => 'custom',
    'zones' => [
        'intro' => [
            'label' => 'Introduction',
            'type' => 'rich_text',
            'required' => false,
        ],
        'plans' => [
            'label' => 'Pricing Plans',
            'type' => 'repeater',
            'required' => true,
        ],
    ],
    'settings_schema' => [
        'columns' => [
            'type' => 'select',
            'label' => 'Number of Columns',
            'options' => [
                '2' => '2 Columns',
                '3' => '3 Columns',
                '4' => '4 Columns',
            ],
            'default' => '3',
        ],
        'accent_color' => [
            'type' => 'color',
            'label' => 'Accent Color',
            'default' => '#3b82f6',
        ],
    ],
],
```

---

## Troubleshooting

### Template Not Showing in Admin

1. Check config is correct in `config/wlcms.php`
2. Clear cache: `php artisan optimize:clear`
3. Verify `allow_custom` is `true`
4. Check template persisted to database

### View Not Found Error

1. Verify view path matches file location
2. Use dot notation: `vendor.wlcms.templates.my-template`
3. Check file exists: `resources/views/vendor/wlcms/templates/my-template.blade.php`

### Compilation Errors

1. Run validation: `php artisan wlcms:validate-template my-template --path=vendor.wlcms.templates.my-template`
2. Check Blade syntax
3. Verify all variables are null-safe: `{{ $var ?? 'default' }}`

### Zones Not Rendering

1. Check zone type is correct
2. Verify zone data exists: `@if(!empty($zones['zoneName']))`
3. Use `{!! !!}` for HTML output, not `{{ }}`

---

## Additional Resources

- **Main Documentation:** [WLCMS_Template_System_Requirements.md](./WLCMS_Template_System_Requirements.md)
- **Implementation Tracker:** [TEMPLATE_SYSTEM_IMPLEMENTATION.md](./TEMPLATE_SYSTEM_IMPLEMENTATION.md)
- **Package Repository:** https://github.com/westlinks/wlcms

---

## Support

For issues or questions:
- GitHub Issues: https://github.com/westlinks/wlcms/issues
- Documentation: See README.md in package root

---

**Happy Template Building! 🎨**
