@extends('wlcms::admin.layout')

@section('title', 'Media Library - WLCMS Admin')
@section('page-title', 'Media Library')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Media Files</h3>
    <div class="flex space-x-3">
        <button type="button" 
                onclick="document.getElementById('file-upload').click()"
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
                onclick="document.getElementById('file-upload').click()"
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

<script>
// Global variables for modal functionality
let currentMediaId = null;
let currentMediaData = null;

// Media Modal Functions
function openMediaModal(id) {
    currentMediaId = id;
    
    // Show modal and loading state
    document.getElementById('media-modal').classList.remove('hidden');
    document.getElementById('modal-loading').classList.remove('hidden');
    document.getElementById('modal-image-container').classList.add('hidden');
    document.getElementById('modal-file-preview').classList.add('hidden');
    
    // Fetch media data
    fetch(`{{ url('admin/wlcms/media') }}/${id}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentMediaData = data.media;
            populateModal(data.media);
        } else {
            showMessage('Failed to load media details', 'error');
            closeMediaModal();
        }
    })
    .catch(error => {
        console.error('Error loading media:', error);
        showMessage('Failed to load media details', 'error');
        closeMediaModal();
    });
}

function closeMediaModal() {
    document.getElementById('media-modal').classList.add('hidden');
    currentMediaId = null;
    currentMediaData = null;
}

function populateModal(media) {
    // Hide loading
    document.getElementById('modal-loading').classList.add('hidden');
    
    // Populate header
    document.getElementById('modal-title').textContent = media.name;
    document.getElementById('modal-filename').textContent = media.original_name;
    
    // Show appropriate preview
    if (media.type === 'image') {
        showImagePreview(media);
    } else {
        showFilePreview(media);
    }
    
    // Populate form fields
    document.getElementById('modal-alt-text').value = media.alt_text || '';
    document.getElementById('modal-caption').value = media.caption || '';
    document.getElementById('modal-description').value = media.description || '';
    
    // Populate file information
    document.getElementById('modal-file-size').textContent = media.size_formatted;
    document.getElementById('modal-file-type').textContent = media.mime_type;
    document.getElementById('modal-upload-date').textContent = media.created_at;
    document.getElementById('modal-uploaded-by').textContent = media.uploaded_by;
    
    // Show dimensions for images
    if (media.dimensions) {
        document.getElementById('modal-dimensions').textContent = media.dimensions;
        document.getElementById('modal-dimensions-row').classList.remove('hidden');
    } else {
        document.getElementById('modal-dimensions-row').classList.add('hidden');
    }
    
    // Populate URL copy buttons
    populateUrlButtons(media.urls);
}

function showImagePreview(media) {
    const imageContainer = document.getElementById('modal-image-container');
    const image = document.getElementById('modal-image');
    
    // Use best available image (try large thumbnail first, fallback to original)
    let imageUrl = media.urls.large || media.urls.medium || media.urls.original;
    
    image.src = imageUrl;
    image.alt = media.alt_text || media.name;
    
    // Show image container
    imageContainer.classList.remove('hidden');
    document.getElementById('modal-file-preview').classList.add('hidden');
}

function showFilePreview(media) {
    const filePreview = document.getElementById('modal-file-preview');
    const fileIcon = document.getElementById('modal-file-icon');
    const fileName = document.getElementById('modal-file-name');
    const fileDetails = document.getElementById('modal-file-details');
    
    // Set appropriate icon
    const icons = {
        'document': '📄',
        'video': '🎥',
        'audio': '🎵',
        'file': '📁'
    };
    fileIcon.textContent = icons[media.type] || icons['file'];
    
    // Set file info
    fileName.textContent = media.name;
    fileDetails.textContent = `${media.size_formatted} • ${media.mime_type}`;
    
    // Show file preview
    filePreview.classList.remove('hidden');
    document.getElementById('modal-image-container').classList.add('hidden');
}

function populateUrlButtons(urls) {
    const container = document.getElementById('modal-thumbnail-urls');
    container.innerHTML = '';
    
    // Add thumbnail size buttons
    const sizeLabels = {
        'thumb': '🖼️ Thumbnail (150px)',
        'small': '🖼️ Small (300px)', 
        'medium': '🖼️ Medium (600px)',
        'large': '🖼️ Large (1200px)'
    };
    
    Object.keys(sizeLabels).forEach(size => {
        if (urls[size]) {
            const button = document.createElement('button');
            button.onclick = () => copyUrl(size);
            button.className = 'w-full text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded border text-sm';
            button.textContent = sizeLabels[size];
            container.appendChild(button);
        }
    });
}

function copyUrl(size) {
    if (!currentMediaData || !currentMediaData.urls[size]) {
        showMessage('URL not available', 'error');
        return;
    }
    
    const url = currentMediaData.urls[size];
    navigator.clipboard.writeText(url).then(() => {
        showMessage(`${size.charAt(0).toUpperCase() + size.slice(1)} URL copied to clipboard!`, 'success');
    }).catch(err => {
        console.error('Could not copy URL: ', err);
        showMessage('Failed to copy URL', 'error');
    });
}

function saveMediaMetadata() {
    if (!currentMediaId) return;
    
    const data = {
        alt_text: document.getElementById('modal-alt-text').value,
        caption: document.getElementById('modal-caption').value,
        description: document.getElementById('modal-description').value,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    fetch(`{{ url('admin/wlcms/media') }}/${currentMediaId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.media) {
            showMessage('Metadata updated successfully!', 'success');
            // Update current data
            currentMediaData.alt_text = data.media.alt_text;
            currentMediaData.caption = data.media.caption;
            currentMediaData.description = data.media.description;
        } else {
            showMessage('Failed to update metadata', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving metadata:', error);
        showMessage('Failed to update metadata', 'error');
    });
}

function downloadMedia() {
    if (!currentMediaId) return;
    
    window.open(`{{ url('admin/wlcms/media') }}/${currentMediaId}/download`, '_blank');
}

function deleteMedia() {
    if (!currentMediaId || !currentMediaData) return;
    
    if (confirm(`Are you sure you want to delete "${currentMediaData.name}"? This action cannot be undone.`)) {
        fetch(`{{ url('admin/wlcms/media') }}/${currentMediaId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                showMessage('Media deleted successfully!', 'success');
                closeMediaModal();
                // Reload page to remove deleted item
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showMessage('Failed to delete media', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting media:', error);
            showMessage('Failed to delete media', 'error');
        });
    }
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (document.getElementById('media-modal').classList.contains('hidden')) return;
    
    if (e.key === 'Escape') {
        closeMediaModal();
    }
});

// Click outside to close
document.getElementById('media-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMediaModal();
    }
});

// File upload handler with progress and error handling
document.getElementById('file-upload').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    if (files.length === 0) return;

    // Show upload progress
    showUploadProgress();
    
    const formData = new FormData();
    files.forEach(file => formData.append('files[]', file));
    
    // Add CSRF token
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("wlcms.admin.media.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideUploadProgress();
        
        if (data.uploaded_media && data.uploaded_media.length > 0) {
            // Show success message
            showMessage(data.message || 'Files uploaded successfully!', 'success');
            
            // Check for any errors
            const hasErrors = data.uploaded_media.some(item => item.error);
            if (hasErrors) {
                const errorFiles = data.uploaded_media.filter(item => item.error);
                showMessage(`Some files failed to upload: ${errorFiles.map(f => f.name).join(', ')}`, 'error');
            }
            
            // Reload the page after a short delay to show the new files
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showMessage('No files were uploaded. Please check file size and format.', 'error');
        }
    })
    .catch(error => {
        hideUploadProgress();
        console.error('Upload error:', error);
        showMessage('Upload failed. Please try again.', 'error');
    });
    
    // Clear the input
    e.target.value = '';
});

function showUploadProgress() {
    // Create and show a progress overlay
    const overlay = document.createElement('div');
    overlay.id = 'upload-progress';
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
    overlay.innerHTML = `
        <div class="bg-white rounded-lg p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Uploading files...</p>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hideUploadProgress() {
    const overlay = document.getElementById('upload-progress');
    if (overlay) overlay.remove();
}

function showMessage(message, type = 'success') {
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md ${
        type === 'success' 
            ? 'bg-green-100 text-green-800 border border-green-200' 
            : 'bg-red-100 text-red-800 border border-red-200'
    }`;
    messageEl.innerHTML = `
        <div class="flex items-center">
            <span class="mr-2">${type === 'success' ? '✅' : '❌'}</span>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" 
                    class="ml-4 text-gray-400 hover:text-gray-600">×</button>
        </div>
    `;
    
    document.body.appendChild(messageEl);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageEl.parentNode) messageEl.remove();
    }, 5000);
}

// Drag and drop functionality
let dragCounter = 0;

document.addEventListener('dragenter', function(e) {
    e.preventDefault();
    dragCounter++;
    showDropZone();
});

document.addEventListener('dragover', function(e) {
    e.preventDefault();
});

document.addEventListener('dragleave', function(e) {
    dragCounter--;
    if (dragCounter <= 0) {
        hideDropZone();
    }
});

document.addEventListener('drop', function(e) {
    e.preventDefault();
    dragCounter = 0;
    hideDropZone();
    
    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
        // Trigger upload with dropped files
        triggerUpload(files);
    }
});

function showDropZone() {
    let dropZone = document.getElementById('drop-zone');
    if (!dropZone) {
        dropZone = document.createElement('div');
        dropZone.id = 'drop-zone';
        dropZone.className = 'fixed inset-0 bg-blue-100 bg-opacity-75 z-40 flex items-center justify-center border-4 border-dashed border-blue-400';
        dropZone.innerHTML = `
            <div class="text-center">
                <span class="text-6xl">📁</span>
                <p class="text-xl font-semibold text-blue-800 mt-4">Drop files here to upload</p>
                <p class="text-blue-600">Images, documents, videos supported</p>
            </div>
        `;
        document.body.appendChild(dropZone);
    }
}

function hideDropZone() {
    const dropZone = document.getElementById('drop-zone');
    if (dropZone) dropZone.remove();
}

function triggerUpload(files) {
    // Create temporary input and trigger upload
    const input = document.getElementById('file-upload');
    const dt = new DataTransfer();
    
    files.forEach(file => dt.items.add(file));
    input.files = dt.files;
    
    // Trigger the change event
    input.dispatchEvent(new Event('change'));
}
</script>
@endsection