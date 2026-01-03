// WLCMS Package JavaScript
// Tiptap Editor for WLCMS
import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'

// Initialize Tiptap Editor
function initTiptapEditor(elementId, initialContent = '') {
    console.log('Initializing Tiptap editor for:', elementId);
    
    const editorElement = document.querySelector(`#${elementId}-editor`);
    const textareaElement = document.querySelector(`#${elementId}`);
    const toolbarElement = document.querySelector(`#${elementId}-toolbar`);
    
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
    
    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit.configure({
                history: false,
            }),
        ],
        content: initialContent,
        onUpdate: ({ editor }) => {
            // Update the hidden textarea
            textareaElement.value = editor.getHTML();
        },
    });
    
    // Toolbar button handlers
    const toolbar = toolbarElement;
    
    // Bold
    toolbar.querySelector('[data-action="bold"]').addEventListener('click', () => {
        editor.chain().focus().toggleBold().run();
    });
    
    // Italic
    toolbar.querySelector('[data-action="italic"]').addEventListener('click', () => {
        editor.chain().focus().toggleItalic().run();
    });
    
    // Code
    toolbar.querySelector('[data-action="code"]').addEventListener('click', () => {
        editor.chain().focus().toggleCode().run();
    });
    
    // Headings
    toolbar.querySelector('[data-action="h1"]').addEventListener('click', () => {
        editor.chain().focus().toggleHeading({ level: 1 }).run();
    });
    
    toolbar.querySelector('[data-action="h2"]').addEventListener('click', () => {
        editor.chain().focus().toggleHeading({ level: 2 }).run();
    });
    
    toolbar.querySelector('[data-action="h3"]').addEventListener('click', () => {
        editor.chain().focus().toggleHeading({ level: 3 }).run();
    });
    
    // Lists
    toolbar.querySelector('[data-action="bullet-list"]').addEventListener('click', () => {
        editor.chain().focus().toggleBulletList().run();
    });
    
    toolbar.querySelector('[data-action="ordered-list"]').addEventListener('click', () => {
        editor.chain().focus().toggleOrderedList().run();
    });
    
    // Quote
    toolbar.querySelector('[data-action="blockquote"]').addEventListener('click', () => {
        editor.chain().focus().toggleBlockquote().run();
    });
    
    // Code block
    toolbar.querySelector('[data-action="code-block"]').addEventListener('click', () => {
        editor.chain().focus().toggleCodeBlock().run();
    });
    
    // Undo/Redo
    toolbar.querySelector('[data-action="undo"]').addEventListener('click', () => {
        editor.chain().focus().undo().run();
    });
    
    toolbar.querySelector('[data-action="redo"]').addEventListener('click', () => {
        editor.chain().focus().redo().run();
    });
    
    // Update toolbar button states
    editor.on('selectionUpdate', () => {
        const buttons = toolbar.querySelectorAll('button[data-action]');
        buttons.forEach(button => {
            button.classList.remove('is-active');
        });
        
        if (editor.isActive('bold')) toolbar.querySelector('[data-action="bold"]').classList.add('is-active');
        if (editor.isActive('italic')) toolbar.querySelector('[data-action="italic"]').classList.add('is-active');
        if (editor.isActive('code')) toolbar.querySelector('[data-action="code"]').classList.add('is-active');
        if (editor.isActive('heading', { level: 1 })) toolbar.querySelector('[data-action="h1"]').classList.add('is-active');
        if (editor.isActive('heading', { level: 2 })) toolbar.querySelector('[data-action="h2"]').classList.add('is-active');
        if (editor.isActive('heading', { level: 3 })) toolbar.querySelector('[data-action="h3"]').classList.add('is-active');
        if (editor.isActive('bulletList')) toolbar.querySelector('[data-action="bullet-list"]').classList.add('is-active');
        if (editor.isActive('orderedList')) toolbar.querySelector('[data-action="ordered-list"]').classList.add('is-active');
        if (editor.isActive('blockquote')) toolbar.querySelector('[data-action="blockquote"]').classList.add('is-active');
        if (editor.isActive('codeBlock')) toolbar.querySelector('[data-action="code-block"]').classList.add('is-active');
    });
    
    console.log('Tiptap editor initialized successfully');
    return editor;
}

// Make initTiptapEditor available globally
window.initTiptapEditor = initTiptapEditor;