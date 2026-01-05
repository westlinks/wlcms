{{-- 
Tiptap Rich Text Editor Component
Usage: @include('wlcms::admin.components.editor', ['name' => 'content', 'value' => $content->content, 'label' => 'Content'])
--}}

@php
    $editorId = $name ?? 'editor';
    $editorValue = $value ?? '';
    $editorLabel = $label ?? 'Content';
    $editorRequired = $required ?? false;
@endphp

<div class="tiptap-editor-wrapper">
    <label for="{{ $editorId }}" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $editorLabel }}
        @if($editorRequired)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="editor-container">
        <!-- Toolbar -->
        <div id="{{ $editorId }}-toolbar" class="editor-toolbar">
            <!-- Text formatting -->
            <button type="button" data-action="bold" title="Bold (Ctrl+B)">
                <strong>B</strong>
            </button>
            <button type="button" data-action="italic" title="Italic (Ctrl+I)">
                <em>I</em>
            </button>
            <button type="button" data-action="code" title="Inline Code">
                &lt;/&gt;
            </button>
            
            <div class="separator"></div>
            
            <!-- Headings -->
            <button type="button" data-action="h1" title="Heading 1">H1</button>
            <button type="button" data-action="h2" title="Heading 2">H2</button>
            <button type="button" data-action="h3" title="Heading 3">H3</button>
            
            <div class="separator"></div>
            
            <!-- Lists -->
            <button type="button" data-action="bullet-list" title="Bullet List">•</button>
            <button type="button" data-action="ordered-list" title="Numbered List">1.</button>
            
            <div class="separator"></div>
            
            <!-- Blocks -->
            <button type="button" data-action="blockquote" title="Blockquote">"</button>
            <button type="button" data-action="code-block" title="Code Block">{ }</button>
            
            <div class="separator"></div>
            
            <!-- History -->
            <button type="button" data-action="undo" title="Undo (Ctrl+Z)">↶</button>
            <button type="button" data-action="redo" title="Redo (Ctrl+Y)">↷</button>
            
            <div class="separator"></div>
            
            <!-- Source View -->
            <button type="button" data-action="source" title="View/Edit HTML Source" class="source-toggle">
                <span>HTML</span>
            </button>
        </div>
        
        <!-- Editor -->
        <div id="{{ $editorId }}-editor" class="min-h-[300px] p-4 bg-white prose prose-sm max-w-none focus:outline-none">
            {!! old($editorId, $editorValue) !!}
        </div>
        
        <!-- Source View Textarea (hidden by default) -->
        <textarea id="{{ $editorId }}-source" 
                  class="hidden w-full min-h-[300px] p-3 border-0 border-t border-gray-300 font-mono text-sm bg-gray-50 focus:outline-none"
                  placeholder="HTML source code..."></textarea>
    </div>
    
    <!-- Hidden textarea for form submission -->
    <textarea name="{{ $editorId }}" 
              id="{{ $editorId }}" 
              class="hidden" 
              @if($editorRequired) required @endif>{!! old($editorId, $editorValue) !!}</textarea>
    
    @error($editorId)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
    
    <p class="mt-2 text-sm text-gray-500">
        ✨ Rich text editor with formatting, headings, lists, and more. Content is saved as clean HTML.
    </p>
</div>

@once
    @include('wlcms::admin.components.tiptap-editor')
@endonce

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorElement = document.getElementById('{{ $editorId }}-editor');
    const hiddenTextarea = document.getElementById('{{ $editorId }}');
    const toolbar = document.getElementById('{{ $editorId }}-toolbar');
    
    console.log('Editor initialization for {{ $editorId }}');
    console.log('TipTap available:', typeof window.initTiptapEditor === 'function');
    
    if (typeof window.initTiptapEditor === 'function') {
        // TipTap available - use rich editor
        try {
            console.log('Initializing TipTap editor...');
            initTiptapEditor('{{ $editorId }}', {!! json_encode(old($editorId, $editorValue)) !!});
        } catch (error) {
            console.log('TipTap initialization failed, using fallback:', error);
            setupFallbackEditor();
        }
    } else {
        // TipTap not available - use fallback contenteditable with basic formatting
        console.log('Using fallback editor');
        setupFallbackEditor();
    }
    
    function setupFallbackEditor() {
        console.log('Setting up fallback editor');
        
        if (!editorElement || !toolbar || !hiddenTextarea) {
            console.error('Missing editor elements');
            return;
        }
        
        editorElement.contentEditable = true;
        editorElement.style.minHeight = '300px';
        editorElement.style.border = '1px solid #d1d5db';
        editorElement.style.borderTop = 'none';
        editorElement.style.outline = 'none';
        
        // Add toolbar button functionality
        toolbar.addEventListener('click', function(e) {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                e.preventDefault();
                const button = e.target.tagName === 'BUTTON' ? e.target : e.target.closest('button');
                const action = button.getAttribute('data-action');
                
                console.log('Toolbar action:', action);
                
                // Focus editor before command
                editorElement.focus();
                
                switch(action) {
                    case 'bold':
                        document.execCommand('bold', false, null);
                        button.classList.toggle('is-active');
                        break;
                    case 'italic':
                        document.execCommand('italic', false, null);
                        button.classList.toggle('is-active');
                        break;
                    case 'code':
                        document.execCommand('formatBlock', false, '<code>');
                        break;
                    case 'h1':
                        document.execCommand('formatBlock', false, '<h1>');
                        break;
                    case 'h2':
                        document.execCommand('formatBlock', false, '<h2>');
                        break;
                    case 'h3':
                        document.execCommand('formatBlock', false, '<h3>');
                        break;
                    case 'bullet-list':
                        document.execCommand('insertUnorderedList', false, null);
                        button.classList.toggle('is-active');
                        break;
                    case 'ordered-list':
                        document.execCommand('insertOrderedList', false, null);
                        button.classList.toggle('is-active');
                        break;
                    case 'blockquote':
                        document.execCommand('formatBlock', false, '<blockquote>');
                        break;
                    case 'source':
                        toggleSourceView();
                        break;
                }
                
                // Sync content with hidden textarea
                hiddenTextarea.value = editorElement.innerHTML;
            }
        });
        
        // Sync content with hidden textarea on any change
        editorElement.addEventListener('input', function() {
            hiddenTextarea.value = editorElement.innerHTML;
        });
        
        // Focus the editor initially
        editorElement.focus();
    }
    
    function toggleSourceView() {
        const sourceElement = document.getElementById('{{ $editorId }}-source');
        const sourceButton = toolbar.querySelector('[data-action="source"]');
        
        if (sourceElement.classList.contains('hidden')) {
            // Switch to source mode
            sourceElement.classList.remove('hidden');
            sourceElement.value = editorElement.innerHTML;
            editorElement.style.display = 'none';
            sourceButton.classList.add('is-active');
            sourceElement.focus();
        } else {
            // Switch to visual mode
            sourceElement.classList.add('hidden');
            editorElement.innerHTML = sourceElement.value;
            editorElement.style.display = 'block';
            sourceButton.classList.remove('is-active');
            editorElement.focus();
            hiddenTextarea.value = editorElement.innerHTML;
        }
    }
});
</script>