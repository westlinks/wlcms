@extends('wlcms::layouts.base')

@section('content')
<div class="container">
    <article class="main-content full-width-template">
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
@endsection
