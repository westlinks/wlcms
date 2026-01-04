@if(config('wlcms.layout.mode') === 'embedded')
    {{-- Include content-only version for embedding --}}
    @include('wlcms::admin.media.index-content')
@else
    {{-- Standalone mode with full layout --}}
    @extends('wlcms::admin.layout')

    @section('title', 'Media Library - WLCMS Admin')
    @section('page-title', 'Media Library')

    @section('content')
    @include('wlcms::admin.media.index-content')
    @endsection
@endif
