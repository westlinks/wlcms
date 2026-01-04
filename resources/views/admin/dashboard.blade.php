@if(config('wlcms.layout.mode') === 'embedded')
    {{-- Include content-only version for embedding --}}
    @include('wlcms::admin.dashboard-content')
@else
    {{-- Standalone mode with full layout --}}
    @extends('wlcms::admin.layout')

    @section('title', 'Dashboard - WLCMS Admin')
    @section('page-title', 'Dashboard')

    @section('content')
    @include('wlcms::admin.dashboard-content')
    @endsection
@endif