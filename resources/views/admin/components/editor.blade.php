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
            <button type="button" data-action="bold" title="Bold">
                <strong>B</strong>
            </button>
            <button type="button" data-action="italic" title="Italic">
                <em>I</em>
            </button>
            <button type="button" data-action="code" title="Code">
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
            <button type="button" data-action="blockquote" title="Quote">"</button>
            <button type="button" data-action="code-block" title="Code Block">{ }</button>
            
            <div class="separator"></div>
            
            <!-- History -->
            <button type="button" data-action="undo" title="Undo">↶</button>
            <button type="button" data-action="redo" title="Redo">↷</button>
            
            <div class="separator"></div>
            
            <!-- Source View -->
            <button type="button" data-action="source" title="Source View">&lt;/&gt;</button>
        </div>
        
        <!-- Editor -->
        <div id="{{ $editorId }}-editor" class="prose max-w-none min-h-[200px] border border-gray-300 rounded-md p-4 bg-white focus-within:border-blue-500">
            {!! old($editorId, $editorValue) !!}
        </div>
        
        <!-- Source View Textarea (hidden by default) -->
        <textarea id="{{ $editorId }}-source" 
                  class="hidden w-full h-80 p-3 border border-gray-300 rounded font-mono text-sm"
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
    
    if (typeof window.initTiptapEditor === 'function') {
        // TipTap available - use rich editor
        try {
            initTiptapEditor('{{ $editorId }}', {!! json_encode(old($editorId, $editorValue)) !!});
        } catch (error) {
            console.log('TipTap initialization failed, using fallback:', error);
            setupFallbackEditor();
        }
    } else {
        // TipTap not available - use fallback contenteditable with basic formatting
        setupFallbackEditor();
    }
    
    function setupFallbackEditor() {
        editorElement.contentEditable = true;
        editorElement.style.minHeight = '200px';
        editorElement.focus();
        
        // Add toolbar button functionality
        toolbar.addEventListener('click', function(e) {
            if (e.target.tagName === 'BUTTON') {
                e.preventDefault();
                const action = e.target.getAttribute('data-action');
                
                switch(action) {
                    case 'bold':
                        document.execCommand('bold', false, null);
                        break;
                    case 'italic':
                        document.execCommand('italic', false, null);
                        break;
                    case 'underline':
                        document.execCommand('underline', false, null);
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
                        break;
                    case 'ordered-list':
                        document.execCommand('insertOrderedList', false, null);
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
    }
});
</script>