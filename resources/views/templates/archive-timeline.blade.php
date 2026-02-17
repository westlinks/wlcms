<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
<div class="container">
    <article class="main-content archive-timeline-template">
        <h1 class="page-title">{{ $contentItem->title }}</h1>

        {{-- Introduction Zone --}}
        @if(!empty($zones['intro']))
            <div class="intro-zone" style="margin-bottom: 2rem;">
                {!! $zones['intro'] !!}
            </div>
        @endif

        {{-- Year Selector --}}
        @if(($settings['show_year_selector'] ?? true))
            <div class="year-selector" style="margin-bottom: 2rem; text-align: center;">
                <label for="year-select" style="margin-right: 1rem; font-weight: 600;">Select Year:</label>
                <select id="year-select" style="padding: 0.5rem 1rem; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 1rem;">
                    @for($year = date('Y'); $year >= date('Y') - 50; $year--)
                        <option value="{{ $year }}" {{ $year == ($settings['default_year'] ?? date('Y')) ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>
        @endif

        {{-- Timeline Items Zone --}}
        <div class="timeline-zone" style="margin-bottom: 3rem;">
            {!! $zones['timeline_items'] ?? '<p>No timeline entries available.</p>' !!}
        </div>

        {{-- Photo Gallery Zone --}}
        @if(!empty($zones['gallery']))
            <div class="gallery-zone" style="margin-top: 3rem; padding: 2rem; background: #F9FAFB; border-radius: 8px;">
                <h2 style="margin-bottom: 1.5rem; font-weight: 600; font-size: 1.5rem;">Photo Gallery</h2>
                {!! $zones['gallery'] !!}
            </div>
        @endif
    </article>
</div>

<style>
    .timeline-zone {
        position: relative;
        padding-left: 2rem;
    }
    .timeline-zone::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #E5E7EB;
    }
</style>
</x-dynamic-component>
