<x-admin-layout title="Media Management">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Media Library') }}
        </h2>
        
        {{-- WLCMS Assets - Load from package or CDN if available --}}
        {{-- <link href="{{ asset('build/assets/wlcms-d15d8dce.css') }}" rel="stylesheet">
        <script src="{{ asset('build/assets/wlcms-01bc0dea.js') }}" defer></script> --}}
        
        {{-- Media Page JavaScript --}}
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎬 Media index JavaScript loading...');
            
            // Check if elements exist
            console.log('📤 Upload input:', document.getElementById('media_upload'));
            console.log('📁 New folder button:', document.getElementById('new-folder-btn'));
            console.log('🖼️ Media preview items:', document.querySelectorAll('.media-preview'));
            
            // Media upload functionality
            const uploadInput = document.getElementById('media_upload');
            const uploadInputEmpty = document.getElementById('media_upload_empty');
            
            function handleFileUpload(e) {
                console.log('📤 File upload triggered', e.target.files.length, 'files');
                const files = Array.from(e.target.files);
                if (files.length === 0) return;
                
                const progressContainer = document.getElementById('upload-progress');
                const uploadList = document.getElementById('upload-list');
                
                progressContainer.classList.remove('hidden');
                uploadList.innerHTML = '';
                
                files.forEach((file, index) => {
                    console.log('⬆️ Uploading file:', file.name);
                    uploadFile(file, index);
                });
            }
            
            function uploadFile(file, index) {
                const formData = new FormData();
                formData.append('files[]', file);
                
                // Handle folder_id properly - don't append if null
                const folderId = {{ $currentFolder->id ?? 'null' }};
                if (folderId !== null) {
                    formData.append('folder_id', folderId);
                }
                
                formData.append('_token', '{{ csrf_token() }}');
                
                console.log('🚀 Upload details:');
                console.log('  📁 File:', file.name, file.size, 'bytes');
                console.log('  🎯 URL:', '{{ route("wlcms.admin.media.upload") }}');
                console.log('  🔐 CSRF token:', '{{ csrf_token() }}');
                console.log('  📂 Folder ID:', folderId === null ? 'ROOT (no folder_id)' : folderId);
                
                const uploadItem = document.createElement('div');
                uploadItem.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span class="text-sm truncate">${file.name}</span>
                        <span class="text-xs text-gray-500">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                        <div class="bg-blue-600 h-1 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                `;
                
                document.getElementById('upload-list').appendChild(uploadItem);
                
                fetch('{{ route("wlcms.admin.media.upload") }}', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => {
                    console.log('📡 Response status:', response.status);
                    console.log('📄 Response headers:', response.headers.get('content-type'));
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.error('❌ Expected JSON but got HTML response:');
                            console.error('📄 First 500 chars:', text.substring(0, 500));
                            console.error('🔍 Looking for error patterns...');
                            
                            // Look for common error patterns
                            if (text.includes('413') || text.includes('Request Entity Too Large')) {
                                throw new Error('File too large - check server upload limits');
                            } else if (text.includes('404') || text.includes('Not Found')) {
                                throw new Error('Upload route not found - check routing');
                            } else if (text.includes('500') || text.includes('Internal Server Error')) {
                                throw new Error('Server error - check server logs');
                            } else if (text.includes('419') || text.includes('CSRF')) {
                                throw new Error('CSRF token mismatch - try refreshing page');
                            } else {
                                throw new Error('Server returned HTML instead of JSON - check server configuration');
                            }
                        });
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Upload response:', data);
                    if (data.success) {
                        uploadItem.querySelector('.text-xs').textContent = '100%';
                        uploadItem.querySelector('.bg-blue-600').style.width = '100%';
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        let errorMessage = 'Upload failed';
                        if (data.errors && data.errors.length > 0) {
                            const fileError = data.errors.find(err => err.name === file.name);
                            errorMessage = fileError ? fileError.error : data.errors[0].error;
                        } else if (data.message) {
                            errorMessage = data.message;
                        }
                        console.error('❌ Upload error for', file.name, ':', errorMessage);
                        uploadItem.innerHTML = `<div class="text-red-600 text-sm">${file.name}: ${errorMessage}</div>`;
                    }
                })
                .catch(error => {
                    console.error('❌ Upload error:', error);
                    uploadItem.innerHTML = `<div class="text-red-600 text-sm">${file.name}: Upload failed - ${error.message}</div>`;
                });
            }
            
            if (uploadInput) {
                uploadInput.addEventListener('change', handleFileUpload);
                console.log('✅ Upload event listener added to main input');
            }
            
            if (uploadInputEmpty) {
                uploadInputEmpty.addEventListener('change', handleFileUpload);
                console.log('✅ Upload event listener added to empty input');
            }
            
            // New folder functionality
            const newFolderBtn = document.getElementById('new-folder-btn');
            const newFolderModal = document.getElementById('new-folder-modal');
            const cancelFolderBtn = document.getElementById('cancel-folder');
            const newFolderForm = document.getElementById('new-folder-form');
            
            if (newFolderBtn) {
                newFolderBtn.addEventListener('click', () => {
                    console.log('📁 New folder button clicked');
                    if (newFolderModal) {
                        newFolderModal.classList.remove('hidden');
                    } else {
                        console.error('❌ New folder modal not found');
                    }
                });
                console.log('✅ New folder button event listener added');
            } else {
                console.error('❌ New folder button not found');
            }
            
            if (cancelFolderBtn) {
                cancelFolderBtn.addEventListener('click', () => {
                    console.log('❌ Cancel folder button clicked');
                    if (newFolderModal) {
                        newFolderModal.classList.add('hidden');
                    }
                });
            }
            
            if (newFolderForm) {
                newFolderForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    console.log('📝 New folder form submitted');
                    
                    const formData = new FormData(e.target);
                    @if($currentFolder)
                        formData.append('parent_id', {{ $currentFolder->id }});
                    @endif
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    console.log('📤 Sending request to:', '{{ route("wlcms.admin.media.folder.store") }}');
                    console.log('📦 Form data:', Object.fromEntries(formData));
                    
                    fetch('{{ route("wlcms.admin.media.folder.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(response => {
                        console.log('📡 Response status:', response.status);
                        console.log('📡 Response headers:', response.headers);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        
                        return response.json();
                    })
                    .then(data => {
                        console.log('📁 Folder creation result:', data);
                        if (data.success !== false) {
                            newFolderModal.classList.add('hidden');
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to create folder');
                        }
                    })
                    .catch(error => {
                        console.error('❌ Folder creation error:', error);
                        alert('Error creating folder: ' + error.message);
                    });
                });
            }
            
            // Media viewer functionality
            function openMediaViewer(mediaId) {
                console.log('🖼️ Opening media viewer for ID:', mediaId);
                
                // Store media ID for form submissions
                window.currentMediaId = mediaId;
                
                const url = `{{ url(config('wlcms.admin.prefix', 'admin/cms')) }}/media/${mediaId}`;
                console.log('🔗 Fetching URL:', url);
                
                fetch(url)
                    .then(response => {
                        console.log('📡 Media viewer response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('📄 Media viewer data:', data);
                        const modal = document.getElementById('media-viewer-modal');
                        const content = document.getElementById('media-viewer-content');
                        
                        document.getElementById('media-viewer-title').textContent = data.name;
                        document.getElementById('media-viewer-filename').textContent = data.name;
                        document.getElementById('media-viewer-size').textContent = data.human_size;
                        document.getElementById('media-viewer-type').textContent = data.mime_type;
                        document.getElementById('media-viewer-uploaded').textContent = data.created_at;
                        document.getElementById('media-viewer-dimensions').textContent = data.dimensions || 'N/A';
                        
                        // Handle download options - multiple sizes if available
                        const downloadContainer = document.getElementById('media-viewer-downloads');
                        if (data.download_sizes && data.download_sizes.length > 0) {
                            console.log('📏 Multiple download sizes available:', data.download_sizes.length);
                            let downloadHTML = '';
                            for (const [key, sizeData] of Object.entries(data.download_sizes)) {
                                downloadHTML += `
                                    <a href="${sizeData.url}" download class="block w-full text-center bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 mb-1">
                                        ${sizeData.label} - ${sizeData.description}
                                    </a>`;
                            }
                            downloadContainer.innerHTML = downloadHTML;
                        } else {
                            // Fallback single download
                            downloadContainer.innerHTML = `<a href="${data.url}" download class="block w-full text-center bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Download Original</a>`;
                        }
                        
                        if (data.type === 'image') {
                            console.log('🖼️ Loading image URL:', data.url);
                            content.innerHTML = `<div class="flex items-center justify-center h-96">
                                <img src="${data.url}" class="max-h-96 max-w-full object-contain" 
                                    onerror="console.error('❌ Image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display:none" class="text-center p-8"><span class="text-6xl">🖼️</span><p class="mt-4">Image preview unavailable</p><p class="text-sm text-gray-500">URL: ${data.url}</p></div>
                            </div>`;
                        } else if (data.type === 'video') {
                            console.log('🎥 Loading video URL:', data.url);
                            content.innerHTML = `<div class="flex items-center justify-center h-96">
                                <video controls class="max-h-96 max-w-full object-contain" 
                                    preload="metadata"
                                    onerror="console.error('❌ Video failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <source src="${data.url}" type="${data.mime_type}">
                                    Your browser does not support the video tag.
                                </video>
                                <div style="display:none" class="text-center p-8"><span class="text-6xl">🎥</span><p class="mt-4">Video preview unavailable</p><p class="text-sm text-gray-500">URL: ${data.url}</p></div>
                            </div>`;
                        } else if (data.type === 'audio') {
                            content.innerHTML = `<audio controls class="w-full mt-8"><source src="${data.url}" type="${data.mime_type}"></audio>`;
                        } else {
                            content.innerHTML = `<div class="flex items-center justify-center h-full"><div class="text-center"><span class="text-6xl">📄</span><p class="mt-4">Preview not available for this file type</p></div></div>`;
                        }
                        
                        // Populate metadata form fields and store media info
                        document.getElementById('media-alt-text').value = data.alt_text || '';
                        document.getElementById('media-caption').value = data.caption || '';
                        document.getElementById('media-description').value = data.description || '';
                        
                        // Store media name for updates (controller requires this)
                        window.currentMediaName = data.name;
                        
                        modal.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('❌ Media viewer error:', error);
                        alert('Failed to load media details');
                    });
            }
            
            // Close media viewer
            const closeViewerBtn = document.getElementById('close-media-viewer');
            if (closeViewerBtn) {
                closeViewerBtn.addEventListener('click', () => {
                    console.log('❌ Closing media viewer');
                    document.getElementById('media-viewer-modal').classList.add('hidden');
                });
            }
            
            // Close modal with ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('media-viewer-modal');
                    if (modal && !modal.classList.contains('hidden')) {
                        console.log('⌨️ ESC key pressed - closing modal');
                        modal.classList.add('hidden');
                    }
                }
            });
            
            // Close modal when clicking outside
            document.addEventListener('click', (e) => {
                const modal = document.getElementById('media-viewer-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    if (e.target === modal) {
                        console.log('🖱️ Clicked outside modal - closing');
                        modal.classList.add('hidden');
                    }
                }
            });
            
            // Media preview click handlers
            document.addEventListener('click', function(e) {
                const mediaPreview = e.target.closest('.media-preview');
                if (mediaPreview) {
                    e.preventDefault();
                    const mediaId = mediaPreview.getAttribute('data-media-id');
                    console.log('🖼️ Media preview clicked, ID:', mediaId);
                    if (mediaId) {
                        openMediaViewer(mediaId);
                    } else {
                        console.error('❌ No media ID found on clicked element');
                    }
                }
            });
            
            // Handle metadata form submission
            document.addEventListener('submit', function(e) {
                if (e.target.id === 'media-metadata-form') {
                    e.preventDefault();
                    console.log('📝 Updating media metadata');
                    
                    const formData = new FormData(e.target);
                    const mediaId = window.currentMediaId; // Use the stored media ID
                    
                    if (!mediaId) {
                        console.error('❌ No media ID found for update');
                        return;
                    }
                    
                    // Add required name field
                    formData.append('name', window.currentMediaName);
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'PATCH');
                    
                    console.log('📤 Updating media ID:', mediaId, 'with data:', Object.fromEntries(formData));
                    
                    fetch(`{{ url(config('wlcms.admin.prefix', 'admin/cms')) }}/media/${mediaId}`, {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => {
                        console.log('📡 Update response status:', response.status);
                        console.log('📄 Update response headers:', response.headers.get('content-type'));
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            return response.text().then(text => {
                                console.error('❌ Expected JSON but got HTML response:');
                                console.error('📄 First 500 chars:', text.substring(0, 500));
                                throw new Error('Server returned HTML instead of JSON - check server configuration');
                            });
                        }
                        
                        return response.json();
                    })
                    .then(data => {
                        console.log('✅ Metadata updated:', data);
                        // Show success feedback
                        const submitBtn = e.target.querySelector('button[type=\"submit\"]');
                        const originalText = submitBtn.textContent;
                        submitBtn.textContent = 'Updated!';
                        submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                        setTimeout(() => {
                            submitBtn.textContent = originalText;
                            submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                            submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        }, 2000);
                    })
                    .catch(error => {
                        console.error('❌ Metadata update error:', error);
                        alert('Error updating metadata: ' + error.message);
                    });
                }
            });
            
            // Handle delete button separately (prevent it from triggering form submission)
            document.addEventListener('click', function(e) {
                if (e.target.id === 'media-viewer-delete') {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('🗑️ Delete button clicked');
                    
                    if (!confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                        return;
                    }
                    
                    const mediaId = window.currentMediaId;
                    
                    if (!mediaId) {
                        console.error('❌ No media ID found for delete');
                        alert('Error: No media ID found');
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'DELETE');
                    
                    console.log('🗑️ Deleting media ID:', mediaId);
                    
                    fetch(`{{ url(config('wlcms.admin.prefix', 'admin/cms')) }}/media/${mediaId}`, {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => {
                        console.log('📡 Delete response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('✅ File deleted:', data);
                        // Close modal and refresh page
                        document.getElementById('media-viewer-modal').classList.add('hidden');
                        setTimeout(() => location.reload(), 500);
                    })
                    .catch(error => {
                        console.error('❌ Delete error:', error);
                        alert('Error deleting file: ' + error.message);
                    });
                }
            });
            
            console.log('🎉 Media page JavaScript initialization complete!');
        });
        </script>
    </x-slot>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            {{-- Upload Button --}}
            <div class="flex items-center space-x-4">
                <label for="media_upload" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg cursor-pointer flex items-center">
                    <span class="mr-2">📎</span>
                    Upload Files
                </label>
                <input type="file" id="media_upload" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt" class="hidden">
                
                {{-- Folder Navigation --}}
                @if($currentFolder && $currentFolder->parent)
                    <a href="{{ route('wlcms.admin.media.index', ['folder' => $currentFolder->parent->id]) }}"
                       class="text-blue-600 hover:text-blue-800 flex items-center">
                        <span class="mr-1">←</span>
                        Back to {{ $currentFolder->parent->name }}
                    </a>
                @elseif($currentFolder)
                    <a href="{{ route('wlcms.admin.media.index') }}"
                       class="text-blue-600 hover:text-blue-800 flex items-center">
                        <span class="mr-1">←</span>
                        Back to Root
                    </a>
                @endif
            </div>

            {{-- Search and Filters --}}
            <div class="flex items-center space-x-3">
                <form method="GET" class="flex items-center space-x-2">
                    @if(request('folder'))
                        <input type="hidden" name="folder" value="{{ request('folder') }}">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search media..."
                           class="border rounded-lg px-3 py-2 w-64">
                    <select name="type" class="border rounded-lg px-3 py-2">
                        <option value="">All Types</option>
                        <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Images</option>
                        <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Videos</option>
                        <option value="audio" {{ request('type') === 'audio' ? 'selected' : '' }}>Audio</option>
                        <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Documents</option>
                    </select>
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        Filter
                    </button>
                </form>
                
                <button id="new-folder-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    New Folder
                </button>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    @if($folderPath->count() > 0)
        <div class="mb-4">
            <nav class="flex items-center space-x-2 text-sm text-gray-600">
                <a href="{{ route('wlcms.admin.media.index') }}" class="hover:text-blue-600">Media Library</a>
                @foreach($folderPath as $folder)
                    <span>/</span>
                    <a href="{{ route('wlcms.admin.media.index', ['folder' => $folder->id]) }}" 
                       class="hover:text-blue-600 {{ $loop->last ? 'font-medium text-gray-900' : '' }}">
                        {{ $folder->name }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    {{-- Media Grid --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($folders->count() > 0 || $media->count() > 0)
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                    {{-- Folders --}}
                    @foreach($folders as $folder)
                        <div class="group relative">
                            <a href="{{ route('wlcms.admin.media.index', ['folder' => $folder->id]) }}"
                               class="block p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                <div class="flex flex-col items-center">
                                    <span class="text-4xl mb-2">📁</span>
                                    <span class="text-sm font-medium text-center break-words">{{ $folder->name }}</span>
                                    <span class="text-xs text-gray-500 mt-1">{{ $folder->files_count }} files</span>
                                </div>
                            </a>
                            
                            {{-- Folder actions --}}
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="folder-menu-btn p-1 bg-white rounded shadow" data-folder="{{ $folder->id }}">
                                    <span class="text-gray-600">⋮</span>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    {{-- Media Files --}}
                    @foreach($media as $file)
                        <div class="group relative media-item" data-id="{{ $file->id }}">
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer media-preview"
                                 data-media-id="{{ $file->id }}">
                                <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if($file->type === 'image')
                                        @if($file->getThumbnailUrl('medium'))
                                            <img src="{{ $file->getThumbnailUrl('medium') }}" 
                                                 alt="{{ $file->alt_text ?? $file->name }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <img src="{{ $file->url }}" 
                                                 alt="{{ $file->alt_text ?? $file->name }}"
                                                 class="w-full h-full object-cover">
                                        @endif
                                    @elseif($file->type === 'video')
                                        <div class="relative w-full h-full bg-black">
                                            <video class="w-full h-full object-cover" preload="metadata">
                                                <source src="{{ $file->url }}#t=1" type="{{ $file->mime_type }}">
                                            </video>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-white text-2xl">▶️</span>
                                            </div>
                                        </div>
                                    @elseif($file->type === 'audio')
                                        <span class="text-4xl">🎵</span>
                                    @else
                                        <span class="text-4xl">📄</span>
                                    @endif
                                </div>
                                
                                <div class="p-3">
                                    <h4 class="font-medium text-sm truncate" title="{{ $file->name }}">{{ $file->name }}</h4>
                                    <div class="flex justify-between items-center mt-1">
                                        <span class="text-xs text-gray-500">{{ $file->human_size }}</span>
                                        <span class="text-xs text-gray-500">{{ ucfirst($file->type) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Media actions --}}
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="media-menu-btn p-1 bg-white rounded shadow" data-media="{{ $file->id }}">
                                    <span class="text-gray-600">⋮</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                {{-- Pagination --}}
                @if($media->hasPages())
                    <div class="mt-6 pt-6 border-t">
                        {{ $media->appends(request()->all())->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-12">
                <span class="text-6xl">📁</span>
                <h3 class="text-lg font-medium text-gray-900 mt-4">
                    @if(request('search') || request('type'))
                        No media found
                    @else
                        Empty folder
                    @endif
                </h3>
                <p class="text-gray-600 mt-2">
                    @if(request('search') || request('type'))
                        Try adjusting your search or filters.
                    @else
                        Upload some files to get started.
                    @endif
                </p>
                @if(!request('search') && !request('type'))
                    <label for="media_upload_empty" 
                           class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer">
                        Upload Files
                    </label>
                    <input type="file" id="media_upload_empty" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt" class="hidden">
                @endif
            </div>
        @endif
    </div>

    {{-- Upload Progress --}}
    <div id="upload-progress" class="hidden fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 w-80">
        <h4 class="font-medium mb-2">Uploading Files</h4>
        <div class="space-y-2" id="upload-list"></div>
    </div>

    {{-- New Folder Modal --}}
    <div id="new-folder-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-medium mb-4">Create New Folder</h3>
            <form id="new-folder-form">
                <div class="mb-4">
                    <label for="folder_name" class="block text-sm font-medium text-gray-700 mb-2">Folder Name</label>
                    <input type="text" id="folder_name" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancel-folder" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Create Folder
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Media Viewer Modal - Advanced Layout --}}
    <div id="media-viewer-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="max-w-7xl max-h-[90vh] p-4 w-full">
            <div class="bg-white rounded-lg overflow-hidden max-h-full flex">
                {{-- Left Side - Media Preview --}}
                <div class="flex-1 bg-gray-100 flex items-center justify-center min-h-96">
                    <div id="media-viewer-content" class="w-full h-full flex items-center justify-center">
                        {{-- Media content will be loaded here --}}
                    </div>
                </div>
                
                {{-- Right Side - Details & Forms --}}
                <div class="w-80 bg-white flex flex-col">
                    {{-- Header --}}
                    <div class="flex justify-between items-center p-4 border-b">
                        <h3 id="media-viewer-title" class="text-lg font-semibold truncate"></h3>
                        <button id="close-media-viewer" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
                    </div>
                    
                    {{-- Scrollable Content --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        {{-- Basic Info --}}
                        <div class="space-y-2">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Filename</label>
                                <p id="media-viewer-filename" class="text-sm text-gray-900 break-words"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Size</label>
                                    <p id="media-viewer-size" class="text-sm text-gray-900"></p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</label>
                                    <p id="media-viewer-type" class="text-sm text-gray-900"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dimensions</label>
                                    <p id="media-viewer-dimensions" class="text-sm text-gray-900"></p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Uploaded</label>
                                    <p id="media-viewer-uploaded" class="text-sm text-gray-900"></p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Download Options --}}
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide block mb-2">Download</label>
                            <div id="media-viewer-downloads" class="space-y-1">
                                {{-- Download buttons will be populated by JavaScript --}}
                            </div>
                        </div>
                        
                        {{-- Metadata Form --}}
                        <div class="border-t pt-4">
                            <form id="media-metadata-form" class="space-y-4">
                                <div>
                                    <label for="media-alt-text" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Alt Text</label>
                                    <input type="text" id="media-alt-text" name="alt_text" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Describe this image...">
                                </div>
                                
                                <div>
                                    <label for="media-caption" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Caption</label>
                                    <input type="text" id="media-caption" name="caption" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Image caption...">
                                </div>
                                
                                <div>
                                    <label for="media-description" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Description</label>
                                    <textarea id="media-description" name="description" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Detailed description..."></textarea>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button type="submit" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                                        Update
                                    </button>
                                    <button type="button" id="media-viewer-delete" class="bg-red-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-red-700 focus:ring-2 focus:ring-red-500">
                                        Delete
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>