<x-admin-layout title="Migration Tools">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Legacy Migration Tools') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('wlcms.admin.legacy.index') }}" class="hover:text-gray-700">Legacy Integration</a>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
            </svg>
            <span>Migration Tools</span>
        </div>

        <!-- Migration Statistics -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Migration Statistics
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h4a2 2 0 002-2V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-600">Legacy Articles</p>
                                <p class="text-2xl font-bold text-blue-900">{{ number_format($stats['legacy_articles']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-600">Mapped Articles</p>
                                <p class="text-2xl font-bold text-green-900">{{ number_format($stats['mapped_articles']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-yellow-600">Unmapped</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ number_format($stats['unmapped_articles']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-red-600">Sync Errors</p>
                                <p class="text-2xl font-bold text-red-900">{{ number_format($stats['sync_errors']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Migration Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Bulk Migration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Bulk Migration
                    </h3>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">
                            Automatically create mappings for unmapped legacy articles. This will create CMS content items and establish mappings.
                        </p>
                        
                        <form method="POST" action="{{ route('wlcms.admin.legacy.migration.bulk') }}" class="space-y-4">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="batch_size" class="block text-sm font-medium text-gray-700">Batch Size</label>
                                    <select name="batch_size" id="batch_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="10">10 articles</option>
                                        <option value="25" selected>25 articles</option>
                                        <option value="50">50 articles</option>
                                        <option value="100">100 articles</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="content_type" class="block text-sm font-medium text-gray-700">Content Type</label>
                                    <select name="content_type" id="content_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="article">Article</option>
                                        <option value="page">Page</option>
                                        <option value="post">Post</option>
                                        <option value="news">News</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="preserve_hierarchy" value="1" checked 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Preserve hierarchy</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="create_redirects" value="1" checked 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Create redirects</span>
                                </label>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" 
                                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        onclick="return confirm('This will create CMS content for unmapped legacy articles. Are you sure?')">
                                    Start Bulk Migration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sync Operations -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Sync Operations
                    </h3>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">
                            Synchronize content between legacy articles and CMS items for existing mappings.
                        </p>
                        
                        <div class="space-y-3">
                            <form method="POST" action="{{ route('wlcms.admin.legacy.migration.sync-all') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                        onclick="return confirm('This will sync all active mappings. Are you sure?')">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
                                    </svg>
                                    Sync All Mappings
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('wlcms.admin.legacy.migration.retry-errors') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        onclick="return confirm('This will retry failed sync operations. Are you sure?')">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
                                    </svg>
                                    Retry Failed Syncs
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Data Management -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Data Management
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Export -->
                    <div class="text-center">
                        <div class="mx-auto w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h4 class="mt-2 text-sm font-medium text-gray-900">Export Mappings</h4>
                        <p class="mt-1 text-sm text-gray-500">Export mapping data as CSV or JSON</p>
                        <div class="mt-3 space-x-2">
                            <a href="{{ route('wlcms.admin.legacy.migration.export', ['format' => 'csv']) }}" 
                               class="text-sm text-blue-600 hover:text-blue-500">CSV</a>
                            <span class="text-gray-300">|</span>
                            <a href="{{ route('wlcms.admin.legacy.migration.export', ['format' => 'json']) }}" 
                               class="text-sm text-blue-600 hover:text-blue-500">JSON</a>
                        </div>
                    </div>
                    
                    <!-- Import -->
                    <div class="text-center">
                        <div class="mx-auto w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <h4 class="mt-2 text-sm font-medium text-gray-900">Import Mappings</h4>
                        <p class="mt-1 text-sm text-gray-500">Import mapping data from CSV</p>
                        <div class="mt-3">
                            <form method="POST" action="{{ route('wlcms.admin.legacy.migration.import') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="import_file" accept=".csv" class="hidden" id="import-file">
                                <label for="import-file" class="cursor-pointer text-sm text-blue-600 hover:text-blue-500">
                                    Choose File
                                </label>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Cleanup -->
                    <div class="text-center">
                        <div class="mx-auto w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h4 class="mt-2 text-sm font-medium text-gray-900">Cleanup</h4>
                        <p class="mt-1 text-sm text-gray-500">Remove inactive or error mappings</p>
                        <div class="mt-3">
                            <form method="POST" action="{{ route('wlcms.admin.legacy.migration.cleanup') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-sm text-red-600 hover:text-red-500"
                                        onclick="return confirm('This will permanently delete inactive/error mappings. Are you sure?')">
                                    Cleanup
                                </button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Recent Migration Activity
                </h3>
                
                <div class="flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200">
                        <!-- This would be populated with actual recent activity data -->
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        Bulk migration completed
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        25 articles migrated successfully
                                    </p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    2 hours ago
                                </div>
                            </div>
                        </li>
                        
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        Sync all mappings
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        42 mappings synchronized
                                    </p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    1 day ago
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div class="mt-6">
                    <a href="{{ route('wlcms.admin.legacy.migration.activity') }}" 
                       class="text-sm text-blue-600 hover:text-blue-500">
                        View all activity →
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>