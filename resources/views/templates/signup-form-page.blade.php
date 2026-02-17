@extends('wlcms::layouts.base')

@section('content')
<div class="signup-form-page-template" 
     style="min-height: 100vh; background: {{ $settings['background_color'] ?? '#ffffff' }}; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="signup-container" style="max-width: 600px; width: 100%; background: white; border-radius: 12px; padding: 3rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        
        {{-- Optional Logo --}}
        @if(($settings['show_logo'] ?? true))
            <div class="logo-section" style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; font-weight: bold; color: #1F2937;">{{ config('app.name') }}</h1>
            </div>
        @endif

        {{-- Header Content Zone --}}
        @if(!empty($zones['header']))
            <div class="header-zone" style="margin-bottom: 2rem; text-align: center; color: #6B7280;">
                {!! $zones['header'] !!}
            </div>
        @endif

        {{-- Form Zone --}}
        <div class="form-zone" style="margin-bottom: 2rem;">
            {!! $zones['form'] ?? '' !!}
        </div>

        {{-- Footer Content Zone --}}
        @if(!empty($zones['footer']))
            <div class="footer-zone" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #E5E7EB; font-size: 0.875rem; color: #9CA3AF; text-align: center;">
                {!! $zones['footer'] !!}
            </div>
        @endif
    </div>
</div>
@endsection
