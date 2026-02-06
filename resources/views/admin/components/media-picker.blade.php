{{-- Media Picker Modal Component --}}
<div id="media-picker-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
        {{-- Header --}}
        <div class="flex items-center justify-between pb-4 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Select Media</h3>
            <button type="button" id="close-media-picker" class="text-gray-400 hover:text-gray-600 text-2xl">
                &times;
            </button>
        </div>

        {{-- Search and Filter --}}
        <div class="py-4 border-b">
            <div class="flex gap-4">
                <div class="flex-1">
                    <input type="text" 
                           id="media-search" 
                           placeholder="Search media..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <select id="media-type-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="image">Images</option>
                        <option value="video">Videos</option>
                        <option value="document">Documents</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Media Grid --}}
        <div class="py-4" style="max-height: 500px; overflow-y: auto;">
            <div id="media-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                {{-- Media items will be loaded here dynamically --}}
                <div class="col-span-full text-center py-8 text-gray-500">
                    <p>Loading media...</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between pt-4 border-t">
            <div>
                <span id="selected-count" class="text-sm text-gray-600">0 selected</span>
            </div>
            <div class="flex gap-2">
                <button type="button" 
                        id="cancel-media-picker"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button type="button" 
                        id="select-media-button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    Select Media
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Template for media item (will be cloned by JavaScript) --}}
<template id="media-item-template">
    <div class="media-item relative group cursor-pointer border-2 border-transparent rounded-lg overflow-hidden hover:border-blue-500 transition-all"
         data-media-id="">
        <div class="aspect-square bg-gray-200 flex items-center justify-center">
            <img src="" alt="" class="media-thumbnail w-full h-full object-cover" loading="lazy">
        </div>
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity flex items-center justify-center">
            <div class="media-checkbox hidden group-hover:block">
                <input type="checkbox" class="w-5 h-5 text-blue-600 rounded" onclick="event.stopPropagation()">
            </div>
        </div>
        <div class="absolute top-2 right-2 hidden selected-badge">
            <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded">✓</span>
        </div>
        <div class="p-2 bg-white">
            <p class="media-name text-xs text-gray-700 truncate"></p>
        </div>
    </div>
</template>
        </div>
    </div>
</template>

<style>
    .media-item.selected {
        border-color: #2563eb !important;
    }
    .media-item.selected .selected-badge {
        display: block !important;
    }
    .media-item.selected .media-checkbox {
        display: block !important;
    }
    .media-item.selected .media-checkbox input {
        display: block !important;
    }
</style>
