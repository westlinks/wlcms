<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
@push('styles')
<style>
    .hero-section {
        position: relative;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        margin-bottom: 3rem;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .hero-overlay {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #fff;
        padding: 3rem;
        max-width: 800px;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1;
    }
    
    .cta-button {
        display: inline-block;
        padding: 1rem 2rem;
        background: #3b82f6;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        margin-top: 1.5rem;
        transition: background 0.3s;
    }
    
    .cta-button:hover {
        background: #2563eb;
    }
    
    .seasonal-banner {
        background: #fef3c7;
        border: 2px solid #f59e0b;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .seasonal-banner.campaign-active {
        background: #dcfce7;
        border-color: #22c55e;
    }
    
    .seasonal-banner.campaign-closed {
        background: #fee2e2;
        border-color: #ef4444;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .feature-card {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #e5e7eb;
    }
    
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .feature-card h3 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }
    
    .sponsors-section {
        background: #f9fafb;
        padding: 3rem 0;
        margin-top: 3rem;
        border-radius: 8px;
    }
    
    .sponsor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 2rem;
        align-items: center;
        justify-items: center;
        margin-top: 2rem;
    }
    
    .sponsor-logo {
        max-width: 150px;
        height: auto;
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s;
    }
    
    .sponsor-logo:hover {
        filter: grayscale(0%);
        opacity: 1;
    }
</style>
@endpush

{{-- Hero Section --}}
@if(isset($zones['hero']) && !empty($zones['hero']))
<section class="hero-section" 
         style="background-image: url('{{ $settings['hero_background'] ?? '' }}')">
    <div class="hero-overlay">
        {!! $zones['hero'] !!}
        
        @if(isset($settings['cta_button_text']) && !empty($settings['cta_button_text']))
        <a href="{{ $settings['cta_button_link'] ?? '#' }}" class="cta-button">
            {{ $settings['cta_button_text'] }}
        </a>
        @endif
    </div>
</section>
@endif

<div class="container">
    {{-- Seasonal Banner (Conditional) --}}
    @if(isset($zones['seasonal_banner']) && !empty($zones['seasonal_banner']))
        @php
            $status = $settings['campaign_status'] ?? 'pre-registration';
            $bannerClass = match($status) {
                'active' => 'campaign-active',
                'closed' => 'campaign-closed',
                'post-event' => 'campaign-closed',
                default => ''
            };
        @endphp
        
        <div class="seasonal-banner {{ $bannerClass }}">
            {!! $zones['seasonal_banner']['content'] ?? $zones['seasonal_banner'] !!}
        </div>
    @endif

    {{-- Main Content --}}
    <article class="main-content">
        <div class="content-zone">
            {!! $zones['content'] ?? '' !!}
        </div>
    </article>

    {{-- Feature Highlights (Repeater) --}}
    @if(isset($zones['features']) && is_array($zones['features']) && count($zones['features']) > 0)
    <section class="features">
        <div class="features-grid">
            @foreach($zones['features'] as $feature)
            <div class="feature-card">
                <div class="feature-icon">{{ $feature['icon'] ?? '⭐' }}</div>
                <h3>{{ $feature['title'] ?? 'Feature' }}</h3>
                <p>{{ $feature['text'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>

{{-- Sponsor Logos --}}
@if(isset($zones['sponsors']) && is_array($zones['sponsors']) && count($zones['sponsors']) > 0)
<section class="sponsors-section">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 1rem;">Our Sponsors</h2>
        <div class="sponsor-grid">
            @foreach($zones['sponsors'] as $sponsor)
            <img src="{{ $sponsor['url'] ?? $sponsor['thumbnail'] ?? '' }}" 
                 alt="{{ $sponsor['alt'] ?? 'Sponsor' }}"
                 class="sponsor-logo">
            @endforeach
        </div>
    </div>
</section>
@endif
</x-dynamic-component>
