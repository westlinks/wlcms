// WLCMS Package JavaScript
// Modern Laravel 11-12 compliant package

// Tiptap Editor imports
import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'

// Custom HTML extensions to preserve divs and classes
import { CustomDiv, CustomParagraph, CustomLink } from './components/custom-html.js'

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
    
    // Clear the element before TipTap takes over (prevents duplicate content)
    editorElement.innerHTML = '';
    
    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit.configure({
                // Disable the default paragraph to use our custom one
                paragraph: false,
                history: {
                    depth: 50,
                },
                // Configure heading to preserve classes
                heading: {
                    HTMLAttributes: {
                        class: null,
                    },
                },
                // Configure lists to preserve classes
                bulletList: {
                    HTMLAttributes: {
                        class: null,
                    },
                },
                orderedList: {
                    HTMLAttributes: {
                        class: null,
                    },
                },
                listItem: {
                    HTMLAttributes: {
                        class: null,
                    },
                },
                blockquote: {
                    HTMLAttributes: {
                        class: null,
                    },
                },
            }),
            CustomParagraph,
            CustomDiv,
            CustomLink,
        ],
        content: initialContent,
        onCreate: ({ editor }) => {
            // Initialize hidden textarea
            textareaElement.value = editor.getHTML();
            
            // Add click handler for links in the editor
            const editorContent = editorElement.querySelector('.ProseMirror');
            if (editorContent) {
                editorContent.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (link) {
                        e.preventDefault();
                        // Set cursor position to the link
                        const pos = editor.view.posAtDOM(link, 0);
                        editor.commands.setTextSelection({ from: pos, to: pos + link.textContent.length });
                        // Open the link modal
                        setTimeout(() => openLinkModal(), 10);
                    }
                });
            }
        },
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
        if (editor.isActive('link')) toolbarElement.querySelector('[data-action="link"]')?.classList.add('is-active');
    }
    
    // Simple HTML formatter for better readability in source view
    function formatHTML(html) {
        let formatted = '';
        let indent = 0;
        const tab = '    '; // 4 spaces
        
        // Split by tags
        html.split(/(<[^>]+>)/g).forEach(element => {
            if (element.match(/^<\/\w/)) {
                // Closing tag - decrease indent before adding
                indent = Math.max(0, indent - 1);
                formatted += tab.repeat(indent) + element.trim() + '\n';
            } else if (element.match(/^<\w[^>]*[^\/]>$/)) {
                // Opening tag (not self-closing) - add then increase indent
                formatted += tab.repeat(indent) + element.trim() + '\n';
                indent++;
            } else if (element.match(/^<\w[^>]*\/>$/)) {
                // Self-closing tag
                formatted += tab.repeat(indent) + element.trim() + '\n';
            } else if (element.trim().length > 0) {
                // Text content
                formatted += tab.repeat(indent) + element.trim() + '\n';
            }
        });
        
        return formatted.trim();
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
            // Format the HTML for better readability
            sourceElement.value = formatHTML(editor.getHTML());
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
    
    // Link modal functionality
    const linkModal = document.getElementById(`${elementId}-link-modal`);
    const linkUrlInput = document.getElementById(`${elementId}-link-url`);
    const linkTextInput = document.getElementById(`${elementId}-link-text`);
    const linkTargetSelect = document.getElementById(`${elementId}-link-target`);
    const linkSaveBtn = document.getElementById(`${elementId}-link-save`);
    const linkRemoveBtn = document.getElementById(`${elementId}-link-remove`);
    const linkCancelBtn = document.getElementById(`${elementId}-link-cancel`);
    
    function openLinkModal() {
        const previousUrl = editor.getAttributes('link').href;
        const previousTarget = editor.getAttributes('link').target;
        const { from, to } = editor.state.selection;
        const selectedText = editor.state.doc.textBetween(from, to);
        
        linkUrlInput.value = previousUrl || '';
        linkTextInput.value = selectedText || '';
        linkTargetSelect.value = previousTarget || '_blank';
        linkRemoveBtn.style.display = previousUrl ? 'block' : 'none';
        
        linkModal.classList.remove('hidden');
        linkUrlInput.focus();
    }
    
    function closeLinkModal() {
        linkModal.classList.add('hidden');
    }
    
    function saveLinkModal() {
        const url = linkUrlInput.value.trim();
        const text = linkTextInput.value.trim();
        const target = linkTargetSelect.value;
        
        if (!url) {
            closeLinkModal();
            return;
        }
        
        const { from, to } = editor.state.selection;
        const hasSelection = from !== to;
        
        if (text && !hasSelection) {
            // Insert new link with text
            editor.chain().focus().insertContent(`<a href="${url}" target="${target}">${text}</a>`).run();
        } else {
            // Update existing selection or link
            editor.chain().focus().extendMarkRange('link').setLink({ href: url, target: target }).run();
        }
        
        closeLinkModal();
    }
    
    function removeLinkModal() {
        editor.chain().focus().extendMarkRange('link').unsetLink().run();
        closeLinkModal();
    }
    
    if (linkModal && linkSaveBtn && linkCancelBtn && linkRemoveBtn) {
        linkSaveBtn.addEventListener('click', saveLinkModal);
        linkCancelBtn.addEventListener('click', closeLinkModal);
        linkRemoveBtn.addEventListener('click', removeLinkModal);
        
        // Close modal on backdrop click
        linkModal.addEventListener('click', (e) => {
            if (e.target === linkModal) {
                closeLinkModal();
            }
        });
        
        // Handle Enter key in inputs
        linkUrlInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveLinkModal();
            }
        });
        
        linkTextInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveLinkModal();
            }
        });
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
        'link': openLinkModal,
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
 * Field Override Management for Legacy Article Mappings
 */
class FieldOverrideManager {
    constructor(containerId = 'field-overrides', buttonId = 'add-override') {
        this.container = document.getElementById(containerId);
        this.addButton = document.getElementById(buttonId);
        this.initialized = false;
    }

    init() {
        if (this.initialized || !this.container || !this.addButton) {
            return;
        }

        console.log('Initializing Field Override Manager...');
        
        // Add override functionality
        this.addButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.addOverrideRow();
        });

        // Remove override functionality (event delegation)
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.remove-override')) {
                e.preventDefault();
                this.removeOverrideRow(e.target.closest('.override-row'));
            }
        });

        this.initialized = true;
        console.log('Field Override Manager initialized successfully');
    }

    addOverrideRow() {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-4 gap-4 items-center override-row';
        row.innerHTML = `
            <div>
                <input type="text" 
                       name="new_overrides[field_name][]" 
                       placeholder="Field name"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <input type="text" 
                       name="new_overrides[override_value][]" 
                       placeholder="Override value"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <select name="new_overrides[field_type][]" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="string">String</option>
                    <option value="text">Text</option>
                    <option value="integer">Integer</option>
                    <option value="boolean">Boolean</option>
                    <option value="json">JSON</option>
                    <option value="datetime">DateTime</option>
                </select>
            </div>
            <div class="text-center">
                <button type="button" 
                        class="remove-override text-red-600 hover:text-red-800"
                        title="Remove override">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                    </svg>
                </button>
            </div>
        `;
        
        this.container.appendChild(row);
        console.log('Override row added');
    }

    removeOverrideRow(row) {
        row.remove();
        console.log('Override row removed');
    }
}

/**
 * Initialize Field Override Manager
 */
function initFieldOverrideManager() {
    const manager = new FieldOverrideManager();
    manager.init();
    return manager;
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
    
    // Initialize field override manager if elements exist on page
    if (document.getElementById('field-overrides') && document.getElementById('add-override')) {
        initFieldOverrideManager();
    }
}

// Global function exports for template usage
window.initTiptapEditor = initTiptapEditor;
window.initMediaModal = initMediaModal;
window.initFileUpload = initFileUpload;
window.initFieldOverrideManager = initFieldOverrideManager;
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