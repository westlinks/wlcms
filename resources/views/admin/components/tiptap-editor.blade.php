<!-- Tiptap Editor CSS -->
<link rel="stylesheet" href="https://unpkg.com/@tiptap/pm/style/style.css">
<style>
.ProseMirror {
    min-height: 300px;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 0 0 0.375rem 0.375rem;
    border-top: none;
    outline: none;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    width: 100%;
}

.ProseMirror:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.ProseMirror h1 { font-size: 2.25rem; font-weight: 700; margin: 1.5rem 0 1rem 0; }
.ProseMirror h2 { font-size: 1.875rem; font-weight: 600; margin: 1.25rem 0 0.75rem 0; }
.ProseMirror h3 { font-size: 1.5rem; font-weight: 600; margin: 1rem 0 0.5rem 0; }
.ProseMirror p { margin: 0.75rem 0; line-height: 1.6; }
.ProseMirror ul, .ProseMirror ol { margin: 0.75rem 0; padding-left: 1.5rem; }
.ProseMirror blockquote { 
    border-left: 4px solid #e5e7eb; 
    padding-left: 1rem; 
    margin: 1rem 0; 
    font-style: italic; 
    color: #6b7280;
}
.ProseMirror code { 
    background: #f3f4f6; 
    padding: 0.125rem 0.25rem; 
    border-radius: 0.25rem; 
    font-family: monospace; 
}
.ProseMirror pre {
    background: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
    overflow-x: auto;
}
.ProseMirror pre code {
    background: transparent;
    color: inherit;
    padding: 0;
}

.tiptap-editor-wrapper {
    width: 100%;
}

.editor-container {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    overflow: hidden;
}

.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem;
    border-bottom: 1px solid #d1d5db;
    background: #f9fafb;
    width: 100%;
}

.editor-toolbar button {
    padding: 0.375rem 0.75rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
}

.editor-toolbar button:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.editor-toolbar button.is-active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.editor-toolbar .separator {
    width: 1px;
    background: #d1d5db;
    margin: 0.25rem 0;
}
</style>

<!-- Tiptap Editor Scripts -->
<script src="https://unpkg.com/@tiptap/pm@2.1.13/dist/index.umd.js"></script>
<script src="https://unpkg.com/@tiptap/core@2.1.13/dist/index.umd.js"></script>
<script src="https://unpkg.com/@tiptap/starter-kit@2.1.13/dist/index.umd.js"></script>

<script>
function initTiptapEditor(elementId, initialContent = '') {
    const { Editor } = Tiptap.Core;
    const { StarterKit } = Tiptap.StarterKit;
    
    const editor = new Editor({
        element: document.querySelector(`#${elementId}-editor`),
        extensions: [
            StarterKit.configure({
                history: false,
            }),
        ],
        content: initialContent,
        onUpdate: ({ editor }) => {
            // Update the hidden textarea
            document.querySelector(`#${elementId}`).value = editor.getHTML();
        },
    });
    
    // Toolbar button handlers
    const toolbar = document.querySelector(`#${elementId}-toolbar`);
    
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
    
    return editor;
}
</script>