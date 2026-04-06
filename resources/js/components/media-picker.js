/**
 * MediaPicker Component
 * Handles selecting media from the library and attaching to content
 */
export class MediaPicker {
    constructor(options = {}) {
        this.modal = null;
        this.grid = null;
        this.searchInput = null;
        this.typeFilter = null;
        this.selectedMedia = new Set();
        this.mediaData = [];
        this.foldersData = [];
        this.breadcrumb = [];
        this.currentFolderId = null;
        this.callback = options.callback || null;
        this.multiSelect = options.multiSelect !== undefined ? options.multiSelect : false;
        this.mediaType = options.mediaType || null; // Filter to specific type
        this.isInitialized = false;
    }

    /**
     * Initialize the media picker
     */
    init() {
        if (this.isInitialized) return;

        this.modal = document.getElementById('media-picker-modal');
        this.grid = document.getElementById('media-grid');
        this.searchInput = document.getElementById('media-search');
        this.typeFilter = document.getElementById('media-type-filter');

        if (!this.modal || !this.grid) {
            console.error('Media picker elements not found', {
                modal: !!this.modal,
                grid: !!this.grid
            });
            return;
        }

        console.log('Media picker initialized successfully');
        this.bindEvents();
        this.isInitialized = true;
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Close buttons
        document.getElementById('close-media-picker')?.addEventListener('click', () => this.close());
        document.getElementById('cancel-media-picker')?.addEventListener('click', () => this.close());

        // Select button
        document.getElementById('select-media-button')?.addEventListener('click', () => this.confirmSelection());

        // Search
        this.searchInput?.addEventListener('input', (e) => this.handleSearch(e.target.value));

        // Type filter
        this.typeFilter?.addEventListener('change', (e) => this.handleTypeFilter(e.target.value));

        // Upload button
        document.getElementById('upload-media-btn')?.addEventListener('click', () => this.toggleUploadArea());
        document.getElementById('cancel-upload-btn')?.addEventListener('click', () => this.toggleUploadArea());
        
        // File input
        const fileInput = document.getElementById('media-file-input');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileUpload(e));
        }

        // Close on ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Close on backdrop click
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
    }

    /**
     * Open the media picker
     */
    async open(callback = null, options = {}) {
        if (callback) {
            this.callback = callback;
        }
        
        // Apply options
        this.multiSelect = options.multiSelect !== undefined ? options.multiSelect : false;
        this.mediaType = options.mediaType || null;

        // Reset state
        this.selectedMedia.clear();
        this.currentFolderId = null;
        this.searchInput.value = '';
        
        // Set type filter if specified
        if (this.mediaType && this.typeFilter) {
            this.typeFilter.value = this.mediaType;
        }
        
        this.modal.classList.remove('hidden');
        this.updateSelectedCount();
        this.updateSelectButton();

        // Load media
        await this.loadMedia();
    }

    /**
     * Close the media picker
     */
    close() {
        this.modal.classList.add('hidden');
        this.selectedMedia.clear();
        this.searchInput.value = '';
        this.typeFilter.value = '';
    }

    /**
     * Check if modal is open
     */
    isOpen() {
        return this.modal && !this.modal.classList.contains('hidden');
    }

    /**
     * Load media from API
     */
    async loadMedia() {
        try {
            const params = new URLSearchParams();
            if (this.mediaType) {
                params.append('type', this.mediaType);
            }
            if (this.currentFolderId) {
                params.append('folder', this.currentFolderId);
            }

            const url = `/admin/cms/media/list?${params}`;
            console.log('Fetching media from:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', errorText);
                throw new Error(`Failed to load media: ${response.status}`);
            }

            const data = await response.json();
            console.log('Media data received:', data);
            this.mediaData = data.media || [];
            this.foldersData = data.folders || [];
            this.breadcrumb = data.breadcrumb || [];
            this.renderBreadcrumb();
            this.renderGrid();
        } catch (error) {
            console.error('Error loading media:', error);
            this.grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500"><p>Error loading media. Please try again.</p></div>';
        }
    }

    /**
     * Render breadcrumb navigation
     */
    renderBreadcrumb() {
        const breadcrumbEl = document.getElementById('folder-breadcrumb');
        if (!breadcrumbEl) return;

        // Keep only the root button
        const rootBtn = breadcrumbEl.querySelector('.folder-nav-link');
        breadcrumbEl.innerHTML = '';
        breadcrumbEl.appendChild(rootBtn.cloneNode(true));
        
        // Re-bind root button
        breadcrumbEl.querySelector('.folder-nav-link').addEventListener('click', () => {
            this.navigateToFolder(null);
        });

        // Add breadcrumb items
        this.breadcrumb.forEach((item, index) => {
            const separator = document.createElement('span');
            separator.textContent = ' / ';
            separator.className = 'text-gray-400';
            breadcrumbEl.appendChild(separator);

            const link = document.createElement('button');
            link.type = 'button';
            link.className = 'folder-nav-link text-blue-600 hover:text-blue-800 hover:underline';
            link.textContent = item.name;
            link.dataset.folderId = item.id;
            link.addEventListener('click', () => {
                this.navigateToFolder(item.id);
            });
            breadcrumbEl.appendChild(link);
        });
    }

    /**
     * Render grid with folders and media
     */
    renderGrid() {
        const folderTemplate = document.getElementById('folder-item-template');
        const mediaTemplate = document.getElementById('media-item-template');
        
        if (!folderTemplate || !mediaTemplate) {
            console.error('Templates not found');
            return;
        }

        this.grid.innerHTML = '';

        const hasContent = this.foldersData.length > 0 || this.mediaData.length > 0;
        
        if (!hasContent) {
            this.grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500"><p>No media found</p></div>';
            return;
        }

        // Render folders first
        this.foldersData.forEach(folder => {
            const item = folderTemplate.content.cloneNode(true);
            const container = item.querySelector('.folder-item');
            const name = item.querySelector('.folder-name');
            const count = item.querySelector('.folder-count');

            container.setAttribute('data-folder-id', folder.id);
            container.setAttribute('data-folder-name', folder.name);
            name.textContent = folder.name;
            count.textContent = `${folder.file_count} files`;

            container.addEventListener('click', () => {
                this.navigateToFolder(folder.id);
            });

            this.grid.appendChild(item);
        });

        // Render media items
        this.mediaData.forEach(media => {
            const item = mediaTemplate.content.cloneNode(true);
            const container = item.querySelector('.media-item');
            const img = item.querySelector('.media-thumbnail');
            const name = item.querySelector('.media-name');
            const checkbox = item.querySelector('input[type="checkbox"]');

            container.setAttribute('data-media-id', media.id);
            container.setAttribute('data-media-name', media.name);
            container.setAttribute('data-media-path', media.path);
            container.setAttribute('data-media-url', media.url || '');
            container.setAttribute('data-media-thumbnail', media.thumbnail_url || '');

            // Set thumbnail
            if (media.type === 'image') {
                img.src = media.thumbnail_url || media.url;
                img.alt = media.alt_text || media.name;
            } else {
                // For non-images, show icon or placeholder
                img.src = this.getTypeIcon(media.type);
                img.alt = media.name;
            }

            name.textContent = media.name;

            // Click handler
            container.addEventListener('click', () => this.toggleMedia(media.id, container));
            
            // Checkbox handler
            checkbox.addEventListener('change', (e) => {
                e.stopPropagation();
                this.toggleMedia(media.id, container);
            });

            this.grid.appendChild(item);
        });
    }

    /**
     * Navigate to a folder
     */
    async navigateToFolder(folderId) {
        this.currentFolderId = folderId;
        await this.loadMedia();
    }

    /**
     * Toggle upload area visibility
     */
    toggleUploadArea() {
        const uploadArea = document.getElementById('upload-area');
        if (uploadArea) {
            uploadArea.classList.toggle('hidden');
        }
    }

    /**
     * Handle file upload
     */
    async handleFileUpload(event) {
        const files = event.target.files;
        if (!files || files.length === 0) return;

        const uploadProgress = document.getElementById('upload-progress');
        const uploadProgressBar = document.getElementById('upload-progress-bar');
        const uploadStatus = document.getElementById('upload-status');
        
        uploadProgress?.classList.remove('hidden');

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const formData = new FormData();
            formData.append('file', file);
            if (this.currentFolderId) {
                formData.append('folder_id', this.currentFolderId);
            }

            try {
                uploadStatus.textContent = `Uploading ${file.name}... (${i + 1}/${files.length})`;
                uploadProgressBar.style.width = `${((i + 1) / files.length) * 100}%`;

                const response = await fetch('/admin/cms/media', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`Upload failed for ${file.name}`);
                }
            } catch (error) {
                console.error('Upload error:', error);
                uploadStatus.textContent = `Error uploading ${file.name}`;
            }
        }

        // Reset and reload
        event.target.value = '';
        uploadProgress?.classList.add('hidden');
        uploadProgressBar.style.width = '0%';
        this.toggleUploadArea();
        await this.loadMedia();
    }

    /**
     * Handle search with API reload
     */
    async handleSearch(searchTerm) {
        // For now, use client-side filtering
        // TODO: Could enhance to use server-side search
        const term = searchTerm.toLowerCase();
        const filteredMedia = this.mediaData.filter(media => 
            media.name.toLowerCase().includes(term) ||
            (media.alt_text && media.alt_text.toLowerCase().includes(term))
        );
        
        // Re-render with filtered data
        this.renderFilteredGrid(filteredMedia, this.foldersData);
    }

    /**
     * Handle type filter change
     */ 
    async handleTypeFilter(type) {
        this.mediaType = type || null;
        await this.loadMedia();
    }

    /**
     * Render grid with filtered data
     */
    renderFilteredGrid(mediaData, foldersData) {
        const folderTemplate = document.getElementById('folder-item-template');
        const mediaTemplate = document.getElementById('media-item-template');
        
        this.grid.innerHTML = '';

        const hasContent = foldersData.length > 0 || mediaData.length > 0;
        
        if (!hasContent) {
            this.grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500"><p>No media found</p></div>';
            return;
        }

        // Render folders
        foldersData.forEach(folder => {
            const item = folderTemplate.content.cloneNode(true);
            const container = item.querySelector('.folder-item');
            const name = item.querySelector('.folder-name');
            const count = item.querySelector('.folder-count');

            container.setAttribute('data-folder-id', folder.id);
            name.textContent = folder.name;
            count.textContent = `${folder.file_count} files`;

            container.addEventListener('click', () => this.navigateToFolder(folder.id));
            this.grid.appendChild(item);
        });

        // Render filtered media
        mediaData.forEach(media => {
            const item = mediaTemplate.content.cloneNode(true);
            const container = item.querySelector('.media-item');
            const img = item.querySelector('.media-thumbnail');
            const name = item.querySelector('.media-name');
            const checkbox = item.querySelector('input[type="checkbox"]');

            container.setAttribute('data-media-id', media.id);
            container.setAttribute('data-media-name', media.name);
            container.setAttribute('data-media-path', media.path);
            container.setAttribute('data-media-url', media.url || '');
            container.setAttribute('data-media-thumbnail', media.thumbnail_url || '');

            if (media.type === 'image') {
                img.src = media.thumbnail_url || media.url;
                img.alt = media.alt_text || media.name;
            } else {
                img.src = this.getTypeIcon(media.type);
                img.alt = media.name;
            }

            name.textContent = media.name;

            container.addEventListener('click', () => this.toggleMedia(media.id, container));
            checkbox.addEventListener('change', (e) => {
                e.stopPropagation();
                this.toggleMedia(media.id, container);
            });

            this.grid.appendChild(item);
        });
    }

    /**
     * Toggle media selection
     */
    toggleMedia(mediaId, element) {
        if (!this.multiSelect) {
            // Single select - clear all others
            this.selectedMedia.clear();
            document.querySelectorAll('.media-item').forEach(item => {
                item.classList.remove('selected');
                item.querySelector('input[type="checkbox"]').checked = false;
            });
        }

        if (this.selectedMedia.has(mediaId)) {
            this.selectedMedia.delete(mediaId);
            element.classList.remove('selected');
            element.querySelector('input[type="checkbox"]').checked = false;
        } else {
            this.selectedMedia.add(mediaId);
            element.classList.add('selected');
            element.querySelector('input[type="checkbox"]').checked = true;
        }

        this.updateSelectedCount();
        this.updateSelectButton();
    }

    /**
     * Update selected count display
     */
    updateSelectedCount() {
        const countEl = document.getElementById('selected-count');
        if (countEl) {
            countEl.textContent = `${this.selectedMedia.size} selected`;
        }
    }

    /**
     * Update select button state
     */
    updateSelectButton() {
        const button = document.getElementById('select-media-button');
        if (button) {
            button.disabled = this.selectedMedia.size === 0;
        }
    }

    /**
     * Confirm selection and return to callback
     */
    confirmSelection() {
        if (this.selectedMedia.size === 0) return;

        const selectedData = Array.from(this.selectedMedia).map(id => {
            const element = document.querySelector(`[data-media-id="${id}"]`);
            return {
                id: id,
                name: element.getAttribute('data-media-name'),
                path: element.getAttribute('data-media-path'),
                url: element.getAttribute('data-media-url'),
                thumbnail_url: element.getAttribute('data-media-thumbnail'),
            };
        });

        if (this.callback) {
            this.callback(this.multiSelect ? selectedData : selectedData[0]);
        }

        this.close();
    }

    /**
     * Get icon for media type
     */
    getTypeIcon(type) {
        const icons = {
            'video': '/images/icons/video.svg',
            'document': '/images/icons/document.svg',
            'audio': '/images/icons/audio.svg'
        };
        return icons[type] || '/images/icons/file.svg';
    }

    /**
     * Reload media data
     */
    async reload() {
        this.mediaData = [];
        await this.loadMedia();
    }
}

// Create global instance
window.mediaPicker = new MediaPicker();

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.mediaPicker.init();
    });
} else {
    window.mediaPicker.init();
}
