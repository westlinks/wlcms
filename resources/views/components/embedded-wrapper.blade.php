{{-- Embedded wrapper that uses host application's layout --}}
<x-dynamic-component :component="$customLayout" :title="$title">
    @isset($pageTitle)
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pageTitle }}
            </h2>
        </x-slot>
    @endisset

    {{-- Content with host app compatible styling --}}
    <div class="space-y-6 dark:text-gray-300">
        {{ $slot }}
    </div>

    @push('styles')
        @vite(['resources/vendor/wlcms/css/wlcms.css'])
        <style>
            /* Dark mode compatibility for embedded WLCMS */
            .dark .bg-white { background-color: rgb(30 41 59); }
            .dark .text-gray-900 { color: rgb(203 213 225); }
            .dark .border-gray-200 { border-color: rgb(71 85 105); }
            .dark .shadow { box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.3); }
        </style>
    @endpush

    @push('scripts')
        @vite(['resources/vendor/wlcms/js/wlcms.js'])
    @endpush
</x-dynamic-component>