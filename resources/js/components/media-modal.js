/**
 * MediaModal Component
 * Modern ES6+ class for managing media preview and editing functionality
 */
export class MediaModal {
    constructor() {
        this.currentMediaId = null;
        this.currentMediaData = null;
        this.modal = null;
        this.isInitialized = false;
    }

    /**
     * Initialize the modal and bind events
     */
    init() {
        if (this.isInitialized) return;

        this.modal = document.getElementById('media-modal');
        if (!this.modal) {
            console.warn('Media modal element not found');
            return;
        }

        this.bindEvents();
        this.isInitialized = true;
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (this.isOpen() && e.key === 'Escape') {
                this.close();
            }
        });

        // Click outside to close
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Close button
        const closeButton = this.modal.querySelector('#close-modal');
        if (closeButton) {
            closeButton.addEventListener('click', () => this.close());
        }

        // Save button
        const saveButton = this.modal.querySelector('#save-metadata');
        if (saveButton) {
            saveButton.addEventListener('click', () => this.saveMetadata());
        }
    }

    /**
     * Open modal with media ID
     */
    open(mediaId) {
        this.currentMediaId = mediaId;
        
        // Show modal and loading state
        this.modal.classList.remove('hidden');
        this.showLoading();
        
        // Fetch media data
        this.fetchMediaData(mediaId);
    }

    /**
     * Close modal and reset state
     */
    close() {
        this.modal.classList.add('hidden');
        this.currentMediaId = null;
        this.currentMediaData = null;
    }

    /**
     * Check if modal is currently open
     */
    isOpen() {
        return this.modal && !this.modal.classList.contains('hidden');
    }

    /**
     * Fetch media data from API
     */
    async fetchMediaData(id) {
        try {
            const response = await fetch(`/admin/cms/media/${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.currentMediaData = data.media;
                this.populateModal(data.media);
            } else {
                this.showError('Failed to load media details');
                this.close();
            }
        } catch (error) {
            console.error('Error loading media:', error);
            this.showError('Failed to load media details');
            this.close();
        }
    }

    /**
     * Show loading state
     */
    showLoading() {
        const loadingEl = document.getElementById('modal-loading');
        const imageContainer = document.getElementById('modal-image-container');
        const filePreview = document.getElementById('modal-file-preview');
        
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (imageContainer) imageContainer.classList.add('hidden');
        if (filePreview) filePreview.classList.add('hidden');
    }

    /**
     * Populate modal with media data
     */
    populateModal(media) {
        // Hide loading
        const loadingEl = document.getElementById('modal-loading');
        if (loadingEl) loadingEl.classList.add('hidden');
        
        // Populate header
        this.setElementText('modal-title', media.name);
        this.setElementText('modal-filename', media.original_name);
        
        // Show appropriate preview
        if (media.type === 'image') {
            this.showImagePreview(media);
        } else {
            this.showFilePreview(media);
        }
        
        // Populate form fields
        this.setElementValue('modal-alt-text', media.alt_text || '');
        this.setElementValue('modal-caption', media.caption || '');
        this.setElementValue('modal-description', media.description || '');
        
        // Populate file information
        this.setElementText('modal-file-size', media.size_formatted);
        this.setElementText('modal-file-type', media.mime_type);
        this.setElementText('modal-upload-date', media.created_at);
        this.setElementText('modal-uploaded-by', media.uploaded_by);
        
        // Show dimensions for images
        if (media.dimensions) {
            this.setElementText('modal-dimensions', media.dimensions);
            this.showElement('modal-dimensions-row');
        } else {
            this.hideElement('modal-dimensions-row');
        }
        
        // Populate URL copy buttons
        this.populateUrlButtons(media.urls);
    }

    /**
     * Show image preview
     */
    showImagePreview(media) {
        const imageContainer = document.getElementById('modal-image-container');
        const image = document.getElementById('modal-image');
        
        // Use best available image (try large thumbnail first, fallback to original)
        const imageUrl = media.urls.large || media.urls.medium || media.urls.original;
        
        if (image) {
            image.src = imageUrl;
            image.alt = media.alt_text || media.name;
        }
        
        // Show image container
        if (imageContainer) imageContainer.classList.remove('hidden');
        this.hideElement('modal-file-preview');
    }

    /**
     * Show file preview for non-images
     */
    showFilePreview(media) {
        const icons = {
            'document': '📄',
            'video': '🎥',
            'audio': '🎵',
            'file': '📁'
        };

        this.setElementText('modal-file-icon', icons[media.type] || icons['file']);
        this.setElementText('modal-file-name', media.name);
        this.setElementText('modal-file-details', `${media.size_formatted} • ${media.mime_type}`);
        
        // Show file preview
        this.showElement('modal-file-preview');
        this.hideElement('modal-image-container');
    }

    /**
     * Populate URL copy buttons
     */
    populateUrlButtons(urls) {
        const container = document.getElementById('modal-thumbnail-urls');
        if (!container) return;
        
        container.innerHTML = '';
        
        const sizeLabels = {
            'thumb': '🖼️ Thumbnail (150px)',
            'small': '🖼️ Small (300px)', 
            'medium': '🖼️ Medium (600px)',
            'large': '🖼️ Large (1200px)'
        };
        
        Object.keys(sizeLabels).forEach(size => {
            if (urls[size]) {
                const button = this.createUrlButton(size, sizeLabels[size]);
                container.appendChild(button);
            }
        });
    }

    /**
     * Create URL copy button
     */
    createUrlButton(size, label) {
        const button = document.createElement('button');
        button.onclick = () => this.copyUrl(size);
        button.className = 'w-full text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded border text-sm';
        button.textContent = label;
        return button;
    }

    /**
     * Copy URL to clipboard
     */
    async copyUrl(size) {
        if (!this.currentMediaData || !this.currentMediaData.urls[size]) {
            this.showError('URL not available');
            return;
        }
        
        try {
            await navigator.clipboard.writeText(this.currentMediaData.urls[size]);
            this.showSuccess(`${size.charAt(0).toUpperCase() + size.slice(1)} URL copied to clipboard!`);
        } catch (err) {
            console.error('Could not copy URL: ', err);
            this.showError('Failed to copy URL');
        }
    }

    /**
     * Save media metadata
     */
    async saveMetadata() {
        if (!this.currentMediaId) return;
        
        const formData = new FormData();
        formData.append('alt_text', this.getElementValue('modal-alt-text'));
        formData.append('caption', this.getElementValue('modal-caption'));
        formData.append('description', this.getElementValue('modal-description'));
        formData.append('_token', this.getCsrfToken());
        
        try {
            const response = await fetch(`/admin/cms/media/${this.currentMediaId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.media) {
                this.showSuccess('Metadata updated successfully!');
                // Update current data
                this.currentMediaData.alt_text = result.media.alt_text;
                this.currentMediaData.caption = result.media.caption;
                this.currentMediaData.description = result.media.description;
            } else {
                this.showError('Failed to update metadata');
            }
        } catch (error) {
            console.error('Error saving metadata:', error);
            this.showError('Failed to update metadata');
        }
    }

    /**
     * Download media file
     */
    downloadMedia() {
        if (!this.currentMediaId) return;
        window.open(`/admin/cms/media/${this.currentMediaId}/download`, '_blank');
    }

    /**
     * Delete media with confirmation
     */
    async deleteMedia() {
        if (!this.currentMediaId || !this.currentMediaData) return;
        
        if (!confirm(`Are you sure you want to delete "${this.currentMediaData.name}"? This action cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch(`/admin/cms/media/${this.currentMediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            });

            const data = await response.json();
            
            if (data.message) {
                this.showSuccess('Media deleted successfully!');
                this.close();
                // Reload page to remove deleted item
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showError('Failed to delete media');
            }
        } catch (error) {
            console.error('Error deleting media:', error);
            this.showError('Failed to delete media');
        }
    }

    // Utility methods
    setElementText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    setElementValue(id, value) {
        const el = document.getElementById(id);
        if (el) el.value = value;
    }

    getElementValue(id) {
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    showElement(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
    }

    hideElement(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    }

    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    showSuccess(message) {
        this.showMessage(message, 'success');
    }

    showError(message) {
        this.showMessage(message, 'error');
    }

    showMessage(message, type = 'success') {
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
}