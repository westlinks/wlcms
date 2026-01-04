{{-- Pure content-only mode - NO package layout/navigation whatsoever --}}
<x-dynamic-component :component="$customLayout" :title="$title">
    @isset($pageTitle)
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pageTitle }}
            </h2>
        </x-slot>
    @endisset

    {{-- Just the content, nothing else --}}
    {{ $slot }}

    @push('styles')
        @vite(['resources/vendor/wlcms/css/wlcms.css'])
    @endpush

    @push('scripts')
        @vite(['resources/vendor/wlcms/js/wlcms.js'])
    @endpush
</x-dynamic-component>