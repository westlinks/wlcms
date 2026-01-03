<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WLCMS Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-4 border-b">
                <h1 class="text-xl font-bold text-gray-800">WLCMS Admin</h1>
            </div>
            <nav class="mt-4">
                <a href="{{ route('wlcms.admin.dashboard') }}" 
                   class="block px-4 py-2 text-gray-700 hover:bg-gray-200 {{ request()->routeIs('wlcms.admin.dashboard') ? 'bg-gray-200' : '' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('wlcms.admin.content.index') }}" 
                   class="block px-4 py-2 text-gray-700 hover:bg-gray-200 {{ request()->routeIs('wlcms.admin.content.*') ? 'bg-gray-200' : '' }}">
                    📝 Content
                </a>
                <a href="{{ route('wlcms.admin.media.index') }}" 
                   class="block px-4 py-2 text-gray-700 hover:bg-gray-200 {{ request()->routeIs('wlcms.admin.media.*') ? 'bg-gray-200' : '' }}">
                    📁 Media
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
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