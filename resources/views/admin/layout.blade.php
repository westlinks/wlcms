<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WLCMS Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- WLCMS Package Assets - Development Mode -->
    <style>
        @import url("{{ asset('vendor/wlcms/css/wlcms.css') }}");
    </style>
    
    <!-- Required for component functionality -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-4 border-b">
                <h1 class="text-xl font-bold text-gray-800">WLCMS Admin</h1>
            </div>
            <nav class="mt-4">
                <a href="{{ route('wlcms.admin.dashboard') }}" 
                   class="block px-4 py-2 text-gray-700 hover:bg-gray-200 {{ request()->routeIs('wlcms.admin.dashboard') ? 'bg-gray-200' : '' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('wlcms.admin.content.index') }}" 
                   class="block px-4 py-2 text-gray-700 hover:bg-gray-200 {{ request()->routeIs('wlcms.admin.content.*') ? 'bg-gray-200' : '' }}">
                    📝 Content
                </a>
                <a href="{{ route('wlcms.admin.media.index') }}" 
                   class="block px-4 py-2 text-gray-700 hover:bg-gray-200 {{ request()->routeIs('wlcms.admin.media.*') ? 'bg-gray-200' : '' }}">
                    📁 Media
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <header class="bg-white shadow-sm border-b p-4">
                <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
            </header>
            <main class="p-6">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition>
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" 
                              @click="show = false">
                            <span class="sr-only">Close</span>
                            ×
                        </span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition>
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" 
                              @click="show = false">
                            <span class="sr-only">Close</span>
                            ×
                        </span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
    
    <!-- TEMPORARY: Inline development version until proper bundling -->
    <script>
        // File Upload Component
        class FileUpload {
            constructor(element) {
                this.element = element;
                this.dropZone = element.querySelector('.file-drop-zone');
                this.input = element.querySelector('input[type="file"]');
                this.progressContainer = element.querySelector('.upload-progress');
                this.init();
            }

            init() {
                if (!this.dropZone || !this.input) return;

                // Handle drag and drop
                this.dropZone.addEventListener('dragover', (e) => this.handleDragOver(e));
                this.dropZone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
                this.dropZone.addEventListener('drop', (e) => this.handleDrop(e));

                // Handle file input change
                this.input.addEventListener('change', (e) => this.handleFileSelect(e));
            }

            handleDragOver(e) {
                e.preventDefault();
                this.dropZone.classList.add('border-blue-500', 'bg-blue-50');
            }

            handleDragLeave(e) {
                e.preventDefault();
                this.dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            }

            handleDrop(e) {
                e.preventDefault();
                this.dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                
                const files = Array.from(e.dataTransfer.files);
                this.uploadFiles(files);
            }

            handleFileSelect(e) {
                const files = Array.from(e.target.files);
                this.uploadFiles(files);
            }

            async uploadFiles(files) {
                const formData = new FormData();
                files.forEach(file => formData.append('files[]', file));

                // Add CSRF token
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                formData.append('_token', token);

                try {
                    this.showProgress();
                    
                    const response = await fetch('/admin/media/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': token
                        }
                    });

                    if (response.ok) {
                        window.location.reload(); // Refresh to show new uploads
                    } else {
                        throw new Error('Upload failed');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('Upload failed: ' + error.message);
                } finally {
                    this.hideProgress();
                }
            }

            showProgress() {
                if (this.progressContainer) {
                    this.progressContainer.style.display = 'block';
                }
            }

            hideProgress() {
                if (this.progressContainer) {
                    this.progressContainer.style.display = 'none';
                }
            }
        }

        // Media Modal Component
        class MediaModal {
            constructor() {
                this.modal = null;
                this.currentAsset = null;
                this.init();
            }

            init() {
                // Create modal if it doesn't exist
                this.createModal();
                
                // Add click handlers to media cards
                document.addEventListener('click', (e) => {
                    const card = e.target.closest('[data-media-id]');
                    if (card) {
                        e.preventDefault();
                        const assetId = card.dataset.mediaId;
                        this.open(assetId);
                    }
                });
            }

            createModal() {
                const modalHTML = `
                    <div id="media-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75">
                        <div class="max-w-4xl max-h-[90vh] w-full mx-4 bg-white rounded-lg overflow-hidden">
                            <div class="flex">
                                <!-- Image Preview -->
                                <div class="flex-1 bg-gray-100 flex items-center justify-center min-h-96">
                                    <div id="image-preview" class="max-w-full max-h-full"></div>
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
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                this.modal = document.getElementById('media-modal');
                
                // Add event listeners
                this.modal.querySelector('#close-modal').addEventListener('click', () => this.close());
                this.modal.addEventListener('click', (e) => {
                    if (e.target === this.modal) this.close();
                });
                this.modal.querySelector('#save-metadata').addEventListener('click', () => this.saveMetadata());
            }

            async open(assetId) {
                try {
                    const response = await fetch(`/admin/media/${assetId}`);
                    if (!response.ok) throw new Error('Failed to fetch asset data');
                    
                    this.currentAsset = await response.json();
                    this.populateModal();
                    this.modal.classList.remove('hidden');
                    this.modal.classList.add('flex');
                } catch (error) {
                    console.error('Error opening modal:', error);
                    alert('Failed to load media details');
                }
            }

            close() {
                this.modal.classList.add('hidden');
                this.modal.classList.remove('flex');
            }

            populateModal() {
                if (!this.currentAsset) return;

                // Update image preview
                const preview = this.modal.querySelector('#image-preview');
                if (this.currentAsset.type === 'image') {
                    preview.innerHTML = `<img src="${this.currentAsset.url}" class="max-w-full max-h-full object-contain" alt="${this.currentAsset.title || 'Image'}">`;
                } else {
                    preview.innerHTML = `
                        <div class="text-center p-8">
                            <div class="text-6xl mb-4">📄</div>
                            <p class="text-gray-600">${this.currentAsset.filename}</p>
                        </div>
                    `;
                }

                // Update metadata panel
                const details = this.modal.querySelector('#media-details');
                details.innerHTML = `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" id="asset-title" value="${this.currentAsset.title || ''}" 
                               class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                        <input type="text" id="asset-alt" value="${this.currentAsset.alt_text || ''}" 
                               class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="asset-description" rows="3" 
                                  class="w-full border border-gray-300 rounded px-3 py-2">${this.currentAsset.description || ''}</textarea>
                    </div>
                    
                    <div class="text-sm text-gray-600">
                        <p><strong>File:</strong> ${this.currentAsset.filename}</p>
                        <p><strong>Size:</strong> ${this.formatFileSize(this.currentAsset.file_size)}</p>
                        <p><strong>Type:</strong> ${this.currentAsset.mime_type}</p>
                        <p><strong>Uploaded:</strong> ${new Date(this.currentAsset.created_at).toLocaleDateString()}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                        <div class="flex">
                            <input type="text" readonly value="${this.currentAsset.url}" 
                                   class="flex-1 border border-gray-300 rounded-l px-3 py-2 bg-gray-50">
                            <button onclick="navigator.clipboard.writeText('${this.currentAsset.url}')" 
                                    class="bg-gray-100 border border-l-0 border-gray-300 rounded-r px-3 py-2 hover:bg-gray-200">
                                Copy
                            </button>
                        </div>
                    </div>
                `;
            }

            async saveMetadata() {
                if (!this.currentAsset) return;

                const title = this.modal.querySelector('#asset-title').value;
                const altText = this.modal.querySelector('#asset-alt').value;
                const description = this.modal.querySelector('#asset-description').value;

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const response = await fetch(`/admin/media/${this.currentAsset.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            title,
                            alt_text: altText,
                            description
                        })
                    });

                    if (response.ok) {
                        this.currentAsset.title = title;
                        this.currentAsset.alt_text = altText;
                        this.currentAsset.description = description;
                        alert('Metadata saved successfully!');
                    } else {
                        throw new Error('Failed to save metadata');
                    }
                } catch (error) {
                    console.error('Error saving metadata:', error);
                    alert('Failed to save metadata');
                }
            }

            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize file upload components
            document.querySelectorAll('.file-upload-container').forEach(element => {
                new FileUpload(element);
            });

            // Initialize media modal
            new MediaModal();
        });
    </script>
</body>
</html>