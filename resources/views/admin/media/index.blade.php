@extends('wlcms::admin.layout')

@section('title', 'Media Library - WLCMS Admin')
@section('page-title', 'Media Library')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Media Files</h3>
    <div class="flex space-x-3">
        <button type="button" 
                onclick="triggerFileUpload()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            📁 Upload Files
        </button>
        <input type="file" id="file-upload" multiple accept="image/*,video/*,.pdf,.doc,.docx" class="hidden">
    </div>
</div>

@if($media->count() > 0)
    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('wlcms.admin.media.index') }}" 
               class="py-2 px-1 border-b-2 {{ !request('type') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                All Files
            </a>
            <a href="{{ route('wlcms.admin.media.index', ['type' => 'image']) }}" 
               class="py-2 px-1 border-b-2 {{ request('type') === 'image' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                🖼️ Images
            </a>
            <a href="{{ route('wlcms.admin.media.index', ['type' => 'document']) }}" 
               class="py-2 px-1 border-b-2 {{ request('type') === 'document' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                📄 Documents
            </a>
            <a href="{{ route('wlcms.admin.media.index', ['type' => 'video']) }}" 
               class="py-2 px-1 border-b-2 {{ request('type') === 'video' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                🎥 Videos
            </a>
        </nav>
    </div>

    <!-- Media Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($media as $item)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer"
                 onclick="openMediaModal({{ $item->id }})">
                <div class="aspect-square bg-gray-100 rounded-t-lg flex items-center justify-center overflow-hidden">
                    @if($item->type === 'image')
                        @php
                            $thumbnailUrl = null;
                            if ($item->thumbnails && isset($item->thumbnails['medium'])) {
                                $thumbnailUrl = Storage::disk($item->disk)->url($item->thumbnails['medium']);
                            } elseif ($item->thumbnails && isset($item->thumbnails['small'])) {
                                $thumbnailUrl = Storage::disk($item->disk)->url($item->thumbnails['small']);
                            } else {
                                // Fallback to original image if no thumbnails
                                $thumbnailUrl = Storage::disk($item->disk)->url($item->path);
                            }
                        @endphp
                        <img src="{{ $thumbnailUrl }}" 
                             alt="{{ $item->alt_text }}"
                             class="w-full h-full object-cover"
                             onerror="this.parentElement.innerHTML='<span class=\'text-4xl\'>🖼️</span>'">
                    @elseif($item->type === 'document')
                        <span class="text-4xl">📄</span>
                    @elseif($item->type === 'video')
                        <span class="text-4xl">🎥</span>
                    @elseif($item->type === 'audio')
                        <span class="text-4xl">🎵</span>
                    @else
                        <span class="text-4xl">📁</span>
                    @endif
                </div>
                <div class="p-3">
                    <h4 class="font-medium text-gray-900 text-sm truncate">{{ $item->name }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($item->type) }} • {{ number_format($item->size / 1024, 1) }}KB</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $media->appends(request()->query())->links() }}
    </div>
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <span class="text-6xl">📁</span>
        <h3 class="text-lg font-semibold text-gray-900 mt-4">No media files yet</h3>
        <p class="text-gray-600 mt-2">Upload your first files to get started with the media library.</p>
        <button type="button" 
                onclick="triggerFileUpload()"
                class="inline-block mt-6 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            📁 Upload Files
        </button>
    </div>
@endif

<!-- Media Preview Modal -->
<div id="media-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl">
        <div class="flex h-full">
            <!-- Image Preview Panel -->
            <div id="modal-image-panel" class="flex-1 bg-gray-100 flex items-center justify-center relative min-h-[400px]">
                <div id="modal-loading" class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                </div>
                
                <div id="modal-image-container" class="hidden w-full h-full flex items-center justify-center p-4">
                    <img id="modal-image" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
                </div>
                
                <div id="modal-file-preview" class="hidden flex flex-col items-center justify-center text-gray-600 p-8">
                    <div id="modal-file-icon" class="text-6xl mb-4"></div>
                    <div id="modal-file-info" class="text-center">
                        <div id="modal-file-name" class="font-medium text-lg text-gray-800"></div>
                        <div id="modal-file-details" class="text-sm text-gray-500 mt-1"></div>
                    </div>
                </div>
                
                <!-- Close Button -->
                <button onclick="closeMediaModal()" 
                        class="absolute top-4 right-4 bg-black bg-opacity-50 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-all">
                    ×
                </button>
            </div>
            
            <!-- Details Panel -->
            <div class="w-80 bg-white border-l border-gray-200 flex flex-col">
                <!-- Header -->
                <div class="p-6 border-b border-gray-200">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900">Media Details</h3>
                    <p id="modal-filename" class="text-sm text-gray-500 mt-1"></p>
                </div>
                
                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Metadata Form -->
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="modal-alt-text" class="block text-sm font-medium text-gray-700 mb-1">
                                Alt Text
                            </label>
                            <input type="text" 
                                   id="modal-alt-text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Describe this image...">
                        </div>
                        
                        <div>
                            <label for="modal-caption" class="block text-sm font-medium text-gray-700 mb-1">
                                Caption
                            </label>
                            <input type="text" 
                                   id="modal-caption" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Add a caption...">
                        </div>
                        
                        <div>
                            <label for="modal-description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea id="modal-description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Add a description..."></textarea>
                        </div>
                        
                        <button onclick="saveMediaMetadata()" 
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Save Changes
                        </button>
                    </div>
                    
                    <!-- File Information -->
                    <div class="px-6 pb-6 border-t border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-3 pt-4">File Information</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">File Size:</span>
                                <span id="modal-file-size" class="text-gray-900"></span>
                            </div>
                            <div id="modal-dimensions-row" class="flex justify-between hidden">
                                <span class="text-gray-500">Dimensions:</span>
                                <span id="modal-dimensions" class="text-gray-900"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Type:</span>
                                <span id="modal-file-type" class="text-gray-900"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Uploaded:</span>
                                <span id="modal-upload-date" class="text-gray-900"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Uploaded by:</span>
                                <span id="modal-uploaded-by" class="text-gray-900"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- URL Copy Section -->
                    <div class="px-6 pb-6 border-t border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-3 pt-4">Copy URLs</h4>
                        <div class="space-y-2">
                            <button onclick="copyUrl('original')" 
                                    class="w-full text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded border text-sm">
                                📄 Original URL
                            </button>
                            <div id="modal-thumbnail-urls" class="space-y-1"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer Actions -->
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <div class="flex space-x-3">
                        <button onclick="downloadMedia()" 
                                class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 text-sm">
                            📥 Download
                        </button>
                        <button onclick="deleteMedia()" 
                                class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 text-sm">
                            🗑️ Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Modal -->
<div id="media-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75">
    <div class="max-w-4xl max-h-[90vh] w-full mx-4 bg-white rounded-lg overflow-hidden">
        <div class="flex">
            <!-- Image Preview -->
            <div class="flex-1 bg-gray-100 flex items-center justify-center min-h-96">
                <div id="image-preview" class="max-w-full max-h-full">
                    <!-- Loading state -->
                    <div class="text-center p-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
                        <p class="mt-2 text-gray-600">Loading...</p>
                    </div>
                </div>
            </div>
            
            <!-- Metadata Panel -->
            <div class="w-80 p-6 bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Media Details</h3>
                    <button id="close-modal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div id="media-details" class="space-y-4">
                    <!-- Content will be populated by JavaScript -->
                </div>
                
                <div class="mt-6 pt-6 border-t">
                    <button id="save-metadata" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize WLCMS Media functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Media page loaded - WLCMS components auto-initialized');
});

// Global function for media card clicks
function openMediaModal(mediaId) {
    if (window.wlcmsMediaModal) {
        window.wlcmsMediaModal.open(mediaId);
    }
}

// Global function for file upload
function triggerFileUpload() {
    if (window.wlcmsFileUpload) {
        window.wlcmsFileUpload.triggerUpload();
    }
}
</script>
@endsection