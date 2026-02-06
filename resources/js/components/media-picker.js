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
        this.searchInput?.addEventListener('input', (e) => this.filterMedia(e.target.value));

        // Type filter
        this.typeFilter?.addEventListener('change', (e) => this.filterByType(e.target.value));

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
    async open(callback = null) {
        if (callback) {
            this.callback = callback;
        }

        this.selectedMedia.clear();
        this.modal.classList.remove('hidden');
        this.updateSelectedCount();
        this.updateSelectButton();

        // Load media if not already loaded
        if (this.mediaData.length === 0) {
            await this.loadMedia();
        } else {
            this.renderMedia();
        }
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
            this.renderMedia();
        } catch (error) {
            console.error('Error loading media:', error);
            this.grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500"><p>Error loading media. Please try again.</p></div>';
        }
    }

    /**
     * Render media items in grid
     */
    renderMedia(filteredData = null) {
        const data = filteredData || this.mediaData;
        const template = document.getElementById('media-item-template');
        
        if (!template) {
            console.error('Media item template not found');
            return;
        }

        this.grid.innerHTML = '';

        if (data.length === 0) {
            this.grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500"><p>No media found</p></div>';
            return;
        }

        data.forEach(media => {
            const item = template.content.cloneNode(true);
            const container = item.querySelector('.media-item');
            const img = item.querySelector('.media-thumbnail');
            const name = item.querySelector('.media-name');
            const checkbox = item.querySelector('input[type="checkbox"]');

            container.setAttribute('data-media-id', media.id);
            container.setAttribute('data-media-name', media.name);
            container.setAttribute('data-media-path', media.path);
            container.setAttribute('data-media-url', media.url || '');

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
                thumbnail: element.querySelector('.media-thumbnail')?.src
            };
        });

        if (this.callback) {
            this.callback(this.multiSelect ? selectedData : selectedData[0]);
        }

        this.close();
    }

    /**
     * Filter media by search term
     */
    filterMedia(searchTerm) {
        const term = searchTerm.toLowerCase();
        const filtered = this.mediaData.filter(media => 
            media.name.toLowerCase().includes(term) ||
            (media.alt_text && media.alt_text.toLowerCase().includes(term))
        );
        this.renderMedia(filtered);
    }

    /**
     * Filter media by type
     */
    filterByType(type) {
        if (!type) {
            this.renderMedia();
            return;
        }

        const filtered = this.mediaData.filter(media => media.type === type);
        this.renderMedia(filtered);
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
