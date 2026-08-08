<x-dynamic-component 
    :component="$layout ?? 'wlcms::layouts.base'" 
    :content-item="$contentItem"
    :settings="$settings"
    :meta="$meta"
>
@php
    $resolverClass = config('wlcms.theme.resolver');
    $configDefaults = config('wlcms.theme.defaults', []);

    if ($resolverClass && class_exists($resolverClass) && method_exists($resolverClass, 'resolve')) {
        $resolved = $resolverClass::resolve();
        $colors = array_merge($configDefaults, $resolved);
    } else {
        $colors = $configDefaults;
    }

    $primaryColor = $colors['primary'] ?? 'currentColor';
    $accentColor  = $colors['accent'] ?? 'currentColor';
@endphp
@push('styles')
<style>
    .editorial-container {
        max-width: 800px;
        margin: 0 auto;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #334155;
        line-height: 1.8;
        --brand-primary: {{ $primaryColor }};
        --brand-accent: {{ $accentColor }};
    }

    .editorial-header-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.75rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }

    .author-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: var(--brand-accent);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        flex-shrink: 0;
        object-fit: cover;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .editorial-badge {
        background-color: #fce7f3;
        color: #be1c64;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.25rem 0.625rem;
        border-radius: 0.25rem;
        display: inline-block;
        margin-bottom: 0.375rem;
    }

    .editorial-title {
        margin: 0 0 0.25rem 0;
        font-size: 1.625rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .editorial-subtitle {
        margin: 0;
        font-size: 0.9375rem;
        color: #64748b;
        font-weight: 500;
    }

    .theme-banner {
        background: linear-gradient(135deg, #be1c64 0%, #d92b78 100%);
        color: #ffffff;
        padding: 1.25rem 1.5rem;
        border-radius: 0.625rem;
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(190, 28, 100, 0.15);
    }

    .theme-banner-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #fce7f3;
        margin-bottom: 0.25rem;
    }

    .theme-banner-text {
        font-size: 1.375rem;
        font-weight: 800;
        font-style: italic;
    }

    /* TipTap Content Zone Styling Overrides */
    .editorial-body-content {
        font-size: 1.0625rem;
    }
    .editorial-body-content p {
        margin-bottom: 1.25rem;
    }
    /* .editorial-body-content p:first-of-type {
        font-size: 1.1875rem;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.7;
    } */
    .editorial-body-content p:first-of-type {
        font-size: 1.25rem;
        font-weight: 500;
        color: #0f172a;
        line-height: 1.6;
        margin-bottom: 1.75rem;
        padding-left: 1rem;
        border-left: 3px solid var(--brand-accent);
    }

    .editorial-body-content h2,
    .editorial-body-content h3 {
        margin: 2.25rem 0 0.75rem 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #13357d;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.375rem;
    }
    .editorial-body-content blockquote {
        background-color: #f1f5f9;
        border-left: 4px solid #13357d;
        border-radius: 0 0.5rem 0.5rem 0;
        padding: 1.25rem 1.5rem;
        margin: 2rem 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #0f172a;
        font-style: italic;
        line-height: 1.6;
    }

    .editorial-footer {
        border-top: 2px solid #e2e8f0;
        padding-top: 1.5rem;
        margin-top: 2rem;
        display: flex;
        justify-content: space-between; /* Pushes signature LEFT and badge RIGHT */
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .editorial-footer-badge {
        background-color: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.5rem 0.875rem;
        border-radius: 0.375rem;
        border: 1px solid #cbd5e1;
        display: inline-block;
        white-space: nowrap;
    }

    .editorial-body-content blockquote {
        background-color: #f1f5f9;
        border-left: 4px solid #13357d;
        border-radius: 0 0.5rem 0.5rem 0;
        padding: 1.25rem 1.5rem;
        margin: 2rem 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #0f172a;
        font-style: italic;
        line-height: 1.6;
    }
    .editorial-body-content blockquote p {
        margin: 0;
    }
</style>
@endpush

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <article class="editorial-container">

        <!-- Header Profile Card -->
        <div class="editorial-header-card">
            {{-- Photo / Initial Avatar --}}
            @if(!empty($settings['author_photo']))
                <img 
                    src="{{ $settings['author_photo'] }}" 
                    alt="{{ $settings['author_name'] ?? $contentItem->title }}" 
                    class="author-avatar"
                />
            @else
                <div class="author-avatar">
                    {{ strtoupper(substr($settings['author_name'] ?? 'AR', 0, 2)) }}
                </div>
            @endif

            <div style="flex-grow: 1;">
                <span class="editorial-badge">
                    {{ $settings['badge_text'] ?? 'Welcome Message' }}
                </span>
                <h1 class="editorial-title">
                    {{ $contentItem->title }}
                </h1>
                <p class="editorial-subtitle">
                    <strong style="color: #13357d;">{{ $settings['author_name'] ?? 'Author' }}</strong> — {{ $settings['author_role'] ?? 'Author Role e.g. Leader' }}
                </p>
            </div>
        </div>

        <!-- Convention Theme Banner (Optional via Settings/Meta) -->
        @if(!empty($settings['convention_theme'] ?? 'Together We Recover'))
            <div class="theme-banner">
                <div class="theme-banner-label">
                    {{ $settings['theme_label'] ?? '2027 Convention Theme' }}
                </div>
                <div class="theme-banner-text">
                    “{{ $settings['convention_theme'] ?? 'Together We Recover' }}”
                </div>
            </div>
        @endif

        <!-- Main Content Zone (TipTap Output) -->
        <div class="content-zone editorial-body-content">
            {!! $zones['content'] ?? '' !!}
        </div>

        <!-- Sign-off Footer -->
        <div class="editorial-footer">
            <div>
                <p style="margin: 0; font-size: 0.9375rem; color: #64748b; font-style: italic;">In love and service,</p>
                <p style="margin: 0.25rem 0 0 0; font-size: 1.25rem; font-weight: 800; color: #13357d;">
                    {{ $settings['author_name'] ?? 'Author' }}
                </p>
                <p style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600;">
                    {{ $settings['author_role'] ?? 'Leader' }}
                </p>
            </div>
            <div>
                <span style="background-color: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 0.5rem 0.875rem; border-radius: 0.375rem; border: 1px solid #cbd5e1; display: inline-block;">
                    {{ $settings['footer_badge'] ?? 'Event Name' }}
                </span>
            </div>
        </div>

    </article>
</div>
</x-dynamic-component>