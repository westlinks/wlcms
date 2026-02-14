<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WLCMS Admin')</title>
    
    <!-- Include app styles (Tailwind), WLCMS styles, and app.js for Alpine.js -->
    @vite(['resources/css/app.css', 'resources/vendor/wlcms/css/wlcms.css', 'resources/js/app.js', 'resources/vendor/wlcms/js/wlcms.js'])
    
    <!-- Required for component functionality -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- SIDEBAR REMOVED - Host app handles navigation -->
        
        <!-- Main Content - Full Width -->
        <div class="w-full">
            <header class="bg-white shadow-sm border-b p-4">
                <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
            </header>
            <main class="p-6">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition>
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" 
                              @click="show = false">
                            <span class="sr-only">Close</span>
                            ×
                        </span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition>
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" 
                              @click="show = false">
                            <span class="sr-only">Close</span>
                            ×
                        </span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>