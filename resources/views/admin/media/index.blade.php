<x-admin-layout title="Media Management">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Media Library') }}
        </h2>
        
        {{-- WLCMS Assets --}}
        <link href="{{ asset('build/assets/wlcms-d15d8dce.css') }}" rel="stylesheet">
        <script src="{{ asset('build/assets/wlcms-01bc0dea.js') }}" defer></script>
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

    {{-- Media Viewer Modal --}}
    <div id="media-viewer-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="max-w-6xl max-h-full p-4 w-full">
            <div class="bg-white rounded-lg overflow-hidden max-h-full flex flex-col">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 id="media-viewer-title" class="text-lg font-medium"></h3>
                    <button id="close-media-viewer" class="text-gray-500 hover:text-gray-700 text-xl">&times;</button>
                </div>
                <div class="flex-1 overflow-hidden">
                    <div id="media-viewer-content" class="h-full"></div>
                </div>
                <div class="p-4 border-t bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <strong>File:</strong> <span id="media-viewer-filename"></span><br>
                            <strong>Size:</strong> <span id="media-viewer-size"></span><br>
                            <strong>Type:</strong> <span id="media-viewer-type"></span>
                        </div>
                        <div>
                            <strong>Uploaded:</strong> <span id="media-viewer-uploaded"></span><br>
                            <strong>Dimensions:</strong> <span id="media-viewer-dimensions"></span><br>
                            <div class="mt-2">
                                <a id="media-viewer-download" href="" download class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                    Download
                                </a>
                                <button id="media-viewer-delete" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 ml-2">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    @push('scripts')
    <script>
        // Media upload functionality
        document.getElementById('media_upload').addEventListener('change', handleFileUpload);
        document.getElementById('media_upload_empty')?.addEventListener('change', handleFileUpload);
        
        function handleFileUpload(e) {
            const files = Array.from(e.target.files);
            if (files.length === 0) return;
            
            const progressContainer = document.getElementById('upload-progress');
            const uploadList = document.getElementById('upload-list');
            
            progressContainer.classList.remove('hidden');
            uploadList.innerHTML = '';
            
            files.forEach((file, index) => {
                uploadFile(file, index);
            });
        }
        
        function uploadFile(file, index) {
            const formData = new FormData();
            formData.append('files[]', file);
            formData.append('folder_id', {{ $currentFolder->id ?? 'null' }});
            formData.append('_token', '{{ csrf_token() }}');
            
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    uploadItem.querySelector('.text-xs').textContent = '100%';
                    uploadItem.querySelector('.bg-blue-600').style.width = '100%';
                    setTimeout(() => {
                        location.reload(); // Refresh to show new files
                    }, 1000);
                } else {
                    uploadItem.innerHTML = `<div class="text-red-600 text-sm">${file.name}: ${data.message}</div>`;
                }
            })
            .catch(error => {
                uploadItem.innerHTML = `<div class="text-red-600 text-sm">${file.name}: Upload failed</div>`;
            });
        }
        
        // New folder functionality
        document.getElementById('new-folder-btn').addEventListener('click', () => {
            document.getElementById('new-folder-modal').classList.remove('hidden');
        });
        
        document.getElementById('cancel-folder').addEventListener('click', () => {
            document.getElementById('new-folder-modal').classList.add('hidden');
        });
        
        document.getElementById('new-folder-form').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('parent_id', {{ $currentFolder->id ?? 'null' }});
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route("wlcms.admin.media.folder.store") }}', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        });
        
        // Media viewer functionality
        function openMediaViewer(mediaId) {
            fetch(`{{ url(config('wlcms.admin.prefix', 'admin/cms')) }}/media/${mediaId}`)
                .then(response => response.json())
                .then(data => {
                    const modal = document.getElementById('media-viewer-modal');
                    const content = document.getElementById('media-viewer-content');
                    
                    document.getElementById('media-viewer-title').textContent = data.name;
                    document.getElementById('media-viewer-filename').textContent = data.name;
                    document.getElementById('media-viewer-size').textContent = data.human_size;
                    document.getElementById('media-viewer-type').textContent = data.mime_type;
                    document.getElementById('media-viewer-uploaded').textContent = data.created_at;
                    document.getElementById('media-viewer-dimensions').textContent = data.dimensions || 'N/A';
                    document.getElementById('media-viewer-download').href = data.url;
                    
                    if (data.type === 'image') {
                        content.innerHTML = `<img src="${data.url}" class="max-w-full max-h-full object-contain mx-auto">`;
                    } else if (data.type === 'video') {
                        content.innerHTML = `<video controls class="max-w-full max-h-full mx-auto"><source src="${data.url}" type="${data.mime_type}"></video>`;
                    } else if (data.type === 'audio') {
                        content.innerHTML = `<audio controls class="w-full mt-8"><source src="${data.url}" type="${data.mime_type}"></audio>`;
                    } else {
                        content.innerHTML = `<div class="flex items-center justify-center h-full"><div class="text-center"><span class="text-6xl">📄</span><p class="mt-4">Preview not available for this file type</p></div></div>`;
                    }
                    
                    modal.classList.remove('hidden');
                });
        }
        
        document.getElementById('close-media-viewer').addEventListener('click', () => {
            document.getElementById('media-viewer-modal').classList.add('hidden');
        });
        
        // Add click listeners to all media preview items
        document.addEventListener('click', function(e) {
            const mediaPreview = e.target.closest('.media-preview');
            if (mediaPreview) {
                e.preventDefault();
                const mediaId = mediaPreview.getAttribute('data-media-id');
                if (mediaId) {
                    openMediaViewer(mediaId);
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
