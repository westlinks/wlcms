<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
<div class="signup-form-page-template min-h-screen flex items-center justify-center p-8" 
     style="background: {{ $settings['background_color'] ?? '#ffffff' }};">
    <div class="max-w-xl w-full bg-white rounded-xl p-12 shadow-2xl">
        
        {{-- Optional Logo --}}
        @if(($settings['show_logo'] ?? true))
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">{{ config('app.name') }}</h1>
            </div>
        @endif

        {{-- Header Content Zone --}}
        @if(!empty($zones['header']))
            <div class="mb-8 text-center text-gray-600">
                {!! $zones['header'] !!}
            </div>
        @endif

        {{-- Form Zone --}}
        <div class="mb-8">
            {!! $zones['form'] ?? '' !!}
        </div>

        {{-- Footer Content Zone --}}
        @if(!empty($zones['footer']))
            <div class="mt-8 pt-8 border-t border-gray-200 text-sm text-gray-400 text-center">
                {!! $zones['footer'] !!}
            </div>
        @endif
    </div>
</div>
</x-dynamic-component>
