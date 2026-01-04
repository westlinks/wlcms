@if(config('wlcms.layout.mode') === 'embedded')
    <x-dynamic-component :component="config('wlcms.layout.host_layout')">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Layout Test
            </h2>
        </x-slot>
@else
    <x-wlcms::admin-layout title="Test Layout - WLCMS Admin" page-title="Layout Test">
@endif
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Modern Laravel Layout Test</h2>
        
        <div class="space-y-4">
            <div class="p-4 bg-green-100 border border-green-300 rounded">
                <h3 class="font-medium text-green-800">✅ Component System Working</h3>
                <p class="text-green-700 mt-1">This page is using the modern Laravel component-based layout system.</p>
            </div>
            
            <div class="p-4 bg-blue-100 border border-blue-300 rounded">
                <h3 class="font-medium text-blue-800">🔧 Layout Mode Detection</h3>
                <p class="text-blue-700 mt-1">
                    Current mode: 
                    <code class="bg-blue-200 px-2 py-1 rounded">
                        {{ config('wlcms.layout.mode', 'standalone') }}
                    </code>
                </p>
            </div>
            
            <div class="p-4 bg-purple-100 border border-purple-300 rounded">
                <h3 class="font-medium text-purple-800">🎨 Component Features</h3>
                <ul class="text-purple-700 mt-1 list-disc list-inside space-y-1">
                    <li>Dynamic layout selection (x-dynamic-component)</li>
                    <li>Embedded mode support</li>
                    <li>Slot-based content areas</li>
                    <li>Modern Laravel patterns</li>
                </ul>
            </div>

            <div class="p-4 bg-yellow-100 border border-yellow-300 rounded">
                <h3 class="font-medium text-yellow-800">📊 Integration Status</h3>
                <p class="text-yellow-700 mt-1">All WLCMS views have been updated to use the AdminLayout component.</p>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t">
            <h3 class="font-medium mb-3">Quick Navigation Test</h3>
            <div class="flex space-x-4">
                <a href="{{ route('wlcms.admin.dashboard') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Dashboard
                </a>
                <a href="{{ route('wlcms.admin.content.index') }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Content
                </a>
                <a href="{{ route('wlcms.admin.media.index') }}" 
                   class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    Media
                </a>
            </div>
        </div>
    </div>
@if(config('wlcms.layout.mode') === 'embedded')
    </x-dynamic-component>
@else
    </x-wlcms::admin-layout>
@endif