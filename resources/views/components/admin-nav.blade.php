<!-- WLCMS NAV TEST -->
<nav class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600 py-3 w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="margin-top: 135px; margin-bottom: -112px;">
        <div class="flex items-center space-x-6 text-sm">
            <a href="{{ route('wlcms.admin.dashboard') }}" 
               class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-medium transition {{ request()->routeIs('wlcms.admin.dashboard') ? 'text-blue-600 dark:text-blue-400' : '' }}">
                Dashboard
            </a>
            <span class="text-slate-400">|</span>
            <a href="{{ route('wlcms.admin.content.index') }}" 
               class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-medium transition {{ request()->routeIs('wlcms.admin.content.*') ? 'text-blue-600 dark:text-blue-400' : '' }}">
                Content
            </a>
            <span class="text-slate-400">|</span>
            <a href="{{ route('wlcms.admin.media.index') }}" 
               class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-medium transition {{ request()->routeIs('wlcms.admin.media.*') ? 'text-blue-600 dark:text-blue-400' : '' }}">
                Media
            </a>
            <span class="text-slate-400">|</span>
            <a href="{{ route('wlcms.admin.form-submissions.index') }}" 
               class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-medium transition {{ request()->routeIs('wlcms.admin.form-submissions.*') || request()->routeIs('wlcms.admin.forms.*') ? 'text-blue-600 dark:text-blue-400' : '' }}">
                Forms
            </a>
            @if(config('wlcms.legacy.enabled', false))
            <span class="text-slate-400">|</span>
            <a href="{{ route('wlcms.admin.legacy.index') }}" 
               class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-medium transition {{ request()->routeIs('wlcms.admin.legacy.*') ? 'text-blue-600 dark:text-blue-400' : '' }}">
                Legacy
            </a>
            @endif
        </div>
    </div>
</nav>
