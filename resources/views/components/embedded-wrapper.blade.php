{{-- Embedded wrapper that uses host application's layout --}}
<x-dynamic-component :component="$customLayout" :title="$title">
    @isset($pageTitle)
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pageTitle }}
            </h2>
        </x-slot>
    @endisset

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </div>

    @push('styles')
        @vite(['resources/vendor/wlcms/css/wlcms.css'])
    @endpush

    @push('scripts')
        @vite(['resources/vendor/wlcms/js/wlcms.js'])
    @endpush
</x-dynamic-component>