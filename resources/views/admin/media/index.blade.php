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

<!-- Media Modal (placeholder) -->
<div id="media-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg max-w-lg w-full mx-4 p-6">
        <h3 class="text-lg font-semibold mb-4">Media Details</h3>
        <p class="text-gray-600">Media modal functionality coming soon!</p>
        <div class="mt-6 flex justify-end">
            <button type="button" 
                    onclick="closeMediaModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openMediaModal(id) {
    // TODO: Fetch and display media details
    document.getElementById('media-modal').classList.remove('hidden');
}

function closeMediaModal() {
    document.getElementById('media-modal').classList.add('hidden');
}

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