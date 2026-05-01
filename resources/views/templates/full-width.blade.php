<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <article class="main-content full-width-template">
        {{-- Page Title --}}
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ $contentItem->title }}</h1>
        
        {{-- Featured Image (if enabled) --}}
        @if(($settings['show_featured_image'] ?? 'no') === 'yes' && isset($settings['featured_image']))
        <div class="featured-image" style="margin-bottom: 2rem;">
            <img src="{{ $settings['featured_image'] }}" alt="{{ $contentItem->title }}" style="width: 100%; height: auto; border-radius: 8px;">
        </div>
        @endif

        {{-- Main Content Zone --}}
        <div class="content-zone">
            {!! $zones['content'] ?? '' !!}
        </div>
    </article>
</div>
</x-dynamic-component>
