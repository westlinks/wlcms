@if(config('wlcms.layout.mode') === 'embedded')
    {{-- Include content-only version for embedding --}}
    @include('wlcms::admin.content.index-content')
@else
    {{-- Standalone mode with full layout --}}
    @extends('wlcms::admin.layout')

    @section('title', 'Content - WLCMS Admin')
    @section('page-title', 'Content Management')

    @section('content')
    @include('wlcms::admin.content.index-content')
    @endsection
@endif