<x-dynamic-component 
    :component="$layout ?? 'wlcms::layouts.base'" 
    :content-item="$contentItem"
    :settings="$settings"
    :meta="$meta"
>
@push('styles')
<style>
    .chairperson-container {
        max-width: 800px;
        margin: 0 auto;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #334155;
        line-height: 1.8;
    }

    .chairperson-header-card {
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

    .chairperson-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, #be1c64 0%, #d92b78 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(190, 28, 100, 0.25);
        object-fit: cover;
    }

    .chairperson-badge {
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

    .chairperson-title {
        margin: 0 0 0.25rem 0;
        font-size: 1.625rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .chairperson-subtitle {
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
    .chairperson-body-content {
        font-size: 1.0625rem;
    }
    .chairperson-body-content p {
        margin-bottom: 1.25rem;
    }
    .chairperson-body-content p:first-of-type {
        font-size: 1.1875rem;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.7;
    }
    .chairperson-body-content h2,
    .chairperson-body-content h3 {
        margin: 2.25rem 0 0.75rem 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #13357d;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.375rem;
    }
    .chairperson-body-content blockquote {
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

    .chairperson-footer {
        border-top: 2px solid #e2e8f0;
        padding-top: 1.5rem;
        margin-top: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
</style>
@endpush

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <article class="chairperson-container">

        <!-- Header Profile Card -->
        <div class="chairperson-header-card">
            {{-- Photo / Initial Avatar --}}
            @if(!empty($settings['chairperson_photo']))
                <img 
                    src="{{ $settings['chairperson_photo'] }}" 
                    alt="{{ $settings['chairperson_name'] ?? $contentItem->title }}" 
                    class="chairperson-avatar"
                />
            @else
                <div class="chairperson-avatar">
                    {{ strtoupper(substr($settings['chairperson_name'] ?? 'AR', 0, 2)) }}
                </div>
            @endif

            <div style="flex-grow: 1;">
                <span class="chairperson-badge">
                    {{ $settings['badge_text'] ?? 'Welcome Message' }}
                </span>
                <h1 class="chairperson-title">
                    {{ $contentItem->title }}
                </h1>
                <p class="chairperson-subtitle">
                    <strong style="color: #13357d;">{{ $settings['chairperson_name'] ?? 'Ana R.' }}</strong> — {{ $settings['chairperson_role'] ?? '2027 SFVAAC Chairperson' }}
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
        <div class="content-zone chairperson-body-content">
            {!! $zones['content'] ?? '' !!}
        </div>

        <!-- Sign-off Footer -->
        <div class="chairperson-footer">
            <div>
                <p style="margin: 0; font-size: 0.9375rem; color: #64748b; font-style: italic;">In love and service,</p>
                <p style="margin: 0.25rem 0 0 0; font-size: 1.25rem; font-weight: 800; color: #13357d;">
                    {{ $settings['chairperson_name'] ?? 'Ana R.' }}
                </p>
                <p style="margin: 0; font-size: 0.875rem; color: #475569; font-weight: 600;">
                    {{ $settings['chairperson_role'] ?? '2027 SFVAAC Chairperson' }}
                </p>
            </div>
            <div>
                <span style="background-color: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 0.5rem 0.875rem; border-radius: 0.375rem; border: 1px solid #cbd5e1; display: inline-block;">
                    {{ $settings['footer_badge'] ?? 'SFVAA 51st Annual Convention' }}
                </span>
            </div>
        </div>

    </article>
</div>
</x-dynamic-component>