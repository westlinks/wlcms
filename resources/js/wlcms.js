// WLCMS Package JavaScript
// Modern Laravel 11-12 compliant package

// Tiptap Editor imports
import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'

// WLCMS Component imports
import { MediaModal } from './components/media-modal.js'
import { FileUpload } from './components/file-upload.js'

// Global instances
let mediaModalInstance = null;
let fileUploadInstance = null;

// Initialize Tiptap Editor
function initTiptapEditor(elementId, initialContent = '') {
    console.log('Initializing Tiptap editor for:', elementId);
    
    const editorElement = document.querySelector(`#${elementId}-editor`);
    const textareaElement = document.querySelector(`#${elementId}`);
    const toolbarElement = document.querySelector(`#${elementId}-toolbar`);
    const sourceElement = document.querySelector(`#${elementId}-source`);
    
    if (!editorElement) {
        console.error('Editor element not found:', `#${elementId}-editor`);
        return;
    }
    
    if (!textareaElement) {
        console.error('Textarea element not found:', `#${elementId}`);
        return;
    }
    
    if (!toolbarElement) {
        console.error('Toolbar element not found:', `#${elementId}-toolbar`);
        return;
    }
    
    if (!sourceElement) {
        console.error('Source element not found:', `#${elementId}-source`);
        return;
    }
    
    // Source view toggle state
    let isSourceMode = false;
    
    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit.configure({
                history: {
                    depth: 50,
                },
            }),
        ],
        content: initialContent,
        onUpdate: ({ editor }) => {
            // Update the hidden textarea
            if (!isSourceMode) {
                textareaElement.value = editor.getHTML();
            }
        },
        onFocus: () => {
            updateToolbarState();
        },
        onSelectionUpdate: () => {
            updateToolbarState();
        },
    });
    
    // Function to update toolbar button states
    function updateToolbarState() {
        if (isSourceMode) return;
        
        const buttons = toolbarElement.querySelectorAll('button[data-action]:not([data-action="source"])');
        buttons.forEach(button => {
            button.classList.remove('is-active');
        });
        
        if (editor.isActive('bold')) toolbarElement.querySelector('[data-action="bold"]')?.classList.add('is-active');
        if (editor.isActive('italic')) toolbarElement.querySelector('[data-action="italic"]')?.classList.add('is-active');
        if (editor.isActive('code')) toolbarElement.querySelector('[data-action="code"]')?.classList.add('is-active');
        if (editor.isActive('heading', { level: 1 })) toolbarElement.querySelector('[data-action="h1"]')?.classList.add('is-active');
        if (editor.isActive('heading', { level: 2 })) toolbarElement.querySelector('[data-action="h2"]')?.classList.add('is-active');
        if (editor.isActive('heading', { level: 3 })) toolbarElement.querySelector('[data-action="h3"]')?.classList.add('is-active');
        if (editor.isActive('bulletList')) toolbarElement.querySelector('[data-action="bullet-list"]')?.classList.add('is-active');
        if (editor.isActive('orderedList')) toolbarElement.querySelector('[data-action="ordered-list"]')?.classList.add('is-active');
        if (editor.isActive('blockquote')) toolbarElement.querySelector('[data-action="blockquote"]')?.classList.add('is-active');
        if (editor.isActive('codeBlock')) toolbarElement.querySelector('[data-action="code-block"]')?.classList.add('is-active');
    }
    
    // Source View Toggle
    function toggleSourceView() {
        isSourceMode = !isSourceMode;
        const sourceButton = toolbarElement.querySelector('[data-action="source"]');
        
        if (isSourceMode) {
            // Switch to source mode
            editorElement.style.display = 'none';
            sourceElement.style.display = 'block';
            sourceElement.classList.remove('hidden');
            sourceElement.value = editor.getHTML();
            sourceButton?.classList.add('is-active');
            
            // Clear other button states
            toolbarElement.querySelectorAll('button[data-action]:not([data-action="source"])').forEach(btn => {
                btn.classList.remove('is-active');
                btn.disabled = true;
            });
            
            // Focus on source textarea
            sourceElement.focus();
            
        } else {
            // Switch to visual mode
            try {
                editor.commands.setContent(sourceElement.value);
                textareaElement.value = sourceElement.value;
            } catch (error) {
                console.warn('Invalid HTML in source view, reverting:', error);
                sourceElement.value = editor.getHTML();
            }
            
            editorElement.style.display = 'block';
            sourceElement.style.display = 'none';
            sourceElement.classList.add('hidden');
            sourceButton?.classList.remove('is-active');
            
            // Re-enable toolbar buttons
            toolbarElement.querySelectorAll('button[data-action]:not([data-action="source"])').forEach(btn => {
                btn.disabled = false;
            });
            
            editor.commands.focus();
            updateToolbarState();
        }
    }
    
    // Toolbar button handlers with error handling
    const toolbarHandlers = {
        'bold': () => editor.chain().focus().toggleBold().run(),
        'italic': () => editor.chain().focus().toggleItalic().run(),
        'code': () => editor.chain().focus().toggleCode().run(),
        'h1': () => editor.chain().focus().toggleHeading({ level: 1 }).run(),
        'h2': () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
        'h3': () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
        'bullet-list': () => editor.chain().focus().toggleBulletList().run(),
        'ordered-list': () => editor.chain().focus().toggleOrderedList().run(),
        'blockquote': () => editor.chain().focus().toggleBlockquote().run(),
        'code-block': () => editor.chain().focus().toggleCodeBlock().run(),
        'undo': () => editor.chain().focus().undo().run(),
        'redo': () => editor.chain().focus().redo().run(),
        'source': toggleSourceView
    };
    
    // Add event listeners to toolbar buttons
    Object.keys(toolbarHandlers).forEach(action => {
        const button = toolbarElement.querySelector(`[data-action="${action}"]`);
        if (button) {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                try {
                    toolbarHandlers[action]();
                    if (action !== 'source') {
                        setTimeout(updateToolbarState, 50);
                    }
                } catch (error) {
                    console.error(`Error executing ${action}:`, error);
                }
            });
        }
    });
    
    // Source textarea change handler
    sourceElement.addEventListener('input', () => {
        if (isSourceMode) {
            textareaElement.value = sourceElement.value;
        }
    });
    
    // Initial toolbar state
    setTimeout(updateToolbarState, 100);
    
    console.log('Tiptap editor initialized successfully');
    return editor;
}

/**
 * Initialize Media Modal Component
 */
function initMediaModal() {
    if (!mediaModalInstance) {
        mediaModalInstance = new MediaModal();
        mediaModalInstance.init();
    }
    return mediaModalInstance;
}

/**
 * Initialize File Upload Component
 */
function initFileUpload(uploadUrl, csrfToken) {
    if (!fileUploadInstance) {
        fileUploadInstance = new FileUpload(uploadUrl, csrfToken);
        fileUploadInstance.init();
    }
    return fileUploadInstance;
}

/**
 * Initialize all WLCMS components
 */
function initWlcms(config = {}) {
    console.log('Initializing WLCMS components...');
    
    // Initialize media modal if modal exists on page
    if (document.getElementById('media-modal')) {
        initMediaModal();
    }
    
    // Initialize file upload if upload input exists on page
    if (document.getElementById('file-upload')) {
        const uploadUrl = config.uploadUrl || '/admin/cms/media/upload';
        const csrfToken = config.csrfToken || '';
        initFileUpload(uploadUrl, csrfToken);
    }
}

// Global function exports for template usage
window.initTiptapEditor = initTiptapEditor;
window.initMediaModal = initMediaModal;
window.initFileUpload = initFileUpload;
window.initWlcms = initWlcms;

// Global component access for template functions
window.wlcmsMediaModal = () => mediaModalInstance;
window.wlcmsFileUpload = () => fileUploadInstance;

// Global helper functions for Blade templates
window.openMediaModal = (id) => {
    const modal = mediaModalInstance || initMediaModal();
    modal.open(id);
};

window.closeMediaModal = () => {
    if (mediaModalInstance) {
        mediaModalInstance.close();
    }
};

window.saveMediaMetadata = () => {
    if (mediaModalInstance) {
        mediaModalInstance.saveMetadata();
    }
};

window.downloadMedia = () => {
    if (mediaModalInstance) {
        mediaModalInstance.downloadMedia();
    }
};

window.deleteMedia = () => {
    if (mediaModalInstance) {
        mediaModalInstance.deleteMedia();
    }
};

window.copyUrl = (size) => {
    if (mediaModalInstance) {
        mediaModalInstance.copyUrl(size);
    }
};

window.triggerFileUpload = () => {
    if (fileUploadInstance) {
        fileUploadInstance.triggerFileSelect();
    }
};

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Auto-detect CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Initialize with default config
    initWlcms({
        csrfToken: csrfToken
    });
});