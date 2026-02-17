<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
<div class="container">
    <article class="main-content time-limited-content-template">
        <h1 class="page-title">{{ $contentItem->title }}</h1>

        {{-- Check if content is available based on dates --}}
        @php
            $now = now();
            $availableFrom = !empty($settings['available_from']) ? \Carbon\Carbon::parse($settings['available_from']) : null;
            $availableUntil = !empty($settings['available_until']) ? \Carbon\Carbon::parse($settings['available_until']) : null;
            
            $isAvailable = true;
            if ($availableFrom && $now->lt($availableFrom)) {
                $isAvailable = false;
            }
            if ($availableUntil && $now->gt($availableUntil)) {
                $isAvailable = false;
            }
        @endphp

        @if($isAvailable)
            {{-- Show content when available --}}
            <div class="content-zone">
                {!! $zones['content'] ?? '' !!}
            </div>

            {{-- Downloadable Files --}}
            @if(!empty($zones['files']))
                <div class="files-zone" style="margin-top: 2rem; padding: 1.5rem; background: #F9FAFB; border-radius: 8px;">
                    <h3 style="margin-bottom: 1rem; font-weight: 600;">Available Files</h3>
                    {!! $zones['files'] !!}
                </div>
            @endif

            {{-- Show expiration notice if applicable --}}
            @if($availableUntil)
                <div class="availability-notice" style="margin-top: 2rem; padding: 1rem; background: #FEF3C7; border-left: 4px solid #F59E0B; border-radius: 4px;">
                    <strong>Note:</strong> This content will be available until {{ $availableUntil->format('F j, Y') }}
                </div>
            @endif
        @else
            {{-- Show expiration message --}}
            <div class="expired-notice" style="padding: 3rem; text-align: center; background: #FEE2E2; border-radius: 8px; color: #991B1B;">
                <h2 style="margin-bottom: 1rem;">Content Not Available</h2>
                <p>{{ $settings['expiration_message'] ?? 'This content is no longer available.' }}</p>
            </div>
        @endif
    </article>
</div>
</x-dynamic-component>
