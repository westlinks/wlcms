/**
 * FileUpload Component
 * Modern ES6+ class for handling file uploads with drag & drop functionality
 */
export class FileUpload {
    constructor(uploadUrl, csrfToken) {
        this.uploadUrl = uploadUrl;
        this.csrfToken = csrfToken;
        this.fileInput = null;
        this.dragCounter = 0;
        this.isInitialized = false;
    }

    /**
     * Initialize file upload functionality
     */
    init() {
        if (this.isInitialized) return;

        this.fileInput = document.getElementById('file-upload');
        if (!this.fileInput) {
            console.warn('File upload input not found');
            return;
        }

        this.bindEvents();
        this.isInitialized = true;
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // File input change event
        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));

        // Drag and drop events
        document.addEventListener('dragenter', (e) => this.handleDragEnter(e));
        document.addEventListener('dragover', (e) => this.handleDragOver(e));
        document.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        document.addEventListener('drop', (e) => this.handleDrop(e));
    }

    /**
     * Handle file input change
     */
    handleFileSelect(e) {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        this.uploadFiles(files);
        e.target.value = ''; // Clear the input
    }

    /**
     * Handle drag enter event
     */
    handleDragEnter(e) {
        e.preventDefault();
        this.dragCounter++;
        this.showDropZone();
    }

    /**
     * Handle drag over event
     */
    handleDragOver(e) {
        e.preventDefault();
    }

    /**
     * Handle drag leave event
     */
    handleDragLeave(e) {
        this.dragCounter--;
        if (this.dragCounter <= 0) {
            this.hideDropZone();
        }
    }

    /**
     * Handle drop event
     */
    handleDrop(e) {
        e.preventDefault();
        this.dragCounter = 0;
        this.hideDropZone();
        
        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            this.uploadFiles(files);
        }
    }

    /**
     * Upload files to server
     */
    async uploadFiles(files) {
        this.showUploadProgress();
        
        const formData = new FormData();
        files.forEach(file => formData.append('files[]', file));
        formData.append('_token', this.csrfToken);

        try {
            const response = await fetch(this.uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            this.hideUploadProgress();
            
            if (data.uploaded_media && data.uploaded_media.length > 0) {
                this.showMessage(data.message || 'Files uploaded successfully!', 'success');
                
                // Check for any errors
                const hasErrors = data.uploaded_media.some(item => item.error);
                if (hasErrors) {
                    const errorFiles = data.uploaded_media.filter(item => item.error);
                    this.showMessage(`Some files failed to upload: ${errorFiles.map(f => f.name).join(', ')}`, 'error');
                }
                
                // Reload the page after a short delay to show the new files
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showMessage('No files were uploaded. Please check file size and format.', 'error');
            }
        } catch (error) {
            this.hideUploadProgress();
            console.error('Upload error:', error);
            this.showMessage('Upload failed. Please try again.', 'error');
        }
    }

    /**
     * Show drop zone overlay
     */
    showDropZone() {
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

    /**
     * Hide drop zone overlay
     */
    hideDropZone() {
        const dropZone = document.getElementById('drop-zone');
        if (dropZone) dropZone.remove();
    }

    /**
     * Show upload progress overlay
     */
    showUploadProgress() {
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

    /**
     * Hide upload progress overlay
     */
    hideUploadProgress() {
        const overlay = document.getElementById('upload-progress');
        if (overlay) overlay.remove();
    }

    /**
     * Show message notification
     */
    showMessage(message, type = 'success') {
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

    /**
     * Trigger file selection programmatically
     */
    triggerFileSelect() {
        if (this.fileInput) {
            this.fileInput.click();
        }
    }
}