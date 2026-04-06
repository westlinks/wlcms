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

        {{-- Breadcrumb Navigation --}}
        <div class="py-3 border-b">
            <div class="flex items-center gap-2 text-sm" id="folder-breadcrumb">
                <button type="button" 
                        class="folder-nav-link text-blue-600 hover:text-blue-800 hover:underline" 
                        data-folder-id="">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Root
                </button>
                {{-- Breadcrumb items will be appended here --}}
            </div>
        </div>

        {{-- Search, Filter, and Upload --}}
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
                <div>
                    <button type="button" 
                            id="upload-media-btn"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 whitespace-nowrap">
                        + Upload
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Upload Area (hidden by default) --}}
        <div id="upload-area" class="hidden py-4 border-b bg-gray-50">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-gray-900">Upload Media</h4>
                    <button type="button" id="cancel-upload-btn" class="text-sm text-gray-500 hover:text-gray-700">
                        Cancel
                    </button>
                </div>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                    <input type="file" 
                           id="media-file-input" 
                           class="hidden" 
                           accept="image/*,video/*,.pdf,.doc,.docx"
                           multiple>
                    <label for="media-file-input" class="cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">
                            <span class="font-semibold text-blue-600 hover:text-blue-500">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-500">Images, videos, or documents</p>
                    </label>
                </div>
                <div id="upload-progress" class="hidden">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="upload-progress-bar" class="bg-blue-600 h-2 rounded-full transition-all" style="width: 0%"></div>
                    </div>
                    <p id="upload-status" class="text-sm text-gray-600 mt-1">Uploading...</p>
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
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="media-checkbox hidden group-hover:flex pointer-events-auto">
                <input type="checkbox" class="w-5 h-5 text-blue-600 bg-white border-2 border-gray-300 rounded shadow-lg" onclick="event.stopPropagation()">
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

{{-- Template for folder item (will be cloned by JavaScript) --}}
<template id="folder-item-template">
    <div class="folder-item relative group cursor-pointer border-2 border-gray-300 rounded-lg overflow-hidden hover:border-blue-500 hover:bg-blue-50 transition-all"
         data-folder-id=""
         data-folder-name="">
        <div class="aspect-square bg-gradient-to-br from-blue-100 to-blue-50 flex flex-col items-center justify-center p-4">
            <svg class="w-16 h-16 text-blue-500 mb-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
            </svg>
            <p class="folder-name text-sm font-medium text-gray-900 truncate w-full text-center"></p>
            <p class="folder-count text-xs text-gray-500 mt-1"></p>
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
