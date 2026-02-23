{{-- 
Tiptap Rich Text Editor Component
Usage: @include('wlcms::admin.components.editor', ['name' => 'content', 'value' => $content->content, 'label' => 'Content'])
--}}

@php
    // Sanitize the name to create a valid CSS ID (remove brackets)
    $editorId = str_replace(['[', ']'], ['_', ''], $name ?? 'editor');
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
    
    <div class="editor-container" style="border: 1px solid #d1d5db; border-radius: 0.375rem; overflow: hidden; background: white;">
        <!-- Toolbar -->
        <div id="{{ $editorId }}-toolbar" class="editor-toolbar" style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem; border-bottom: 1px solid #d1d5db; background: #f9fafb;">
            <!-- Text formatting -->
            <button type="button" data-action="bold" title="Bold (Ctrl+B)" 
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">
                <strong>B</strong>
            </button>
            <button type="button" data-action="italic" title="Italic (Ctrl+I)"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">
                <em>I</em>
            </button>
            <button type="button" data-action="code" title="Inline Code"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">
                &lt;/&gt;
            </button>
            <button type="button" data-action="link" title="Insert/Edit Link (Ctrl+K)"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">
                🔗
            </button>
            
            <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
            
            <!-- Headings -->
            <button type="button" data-action="h1" title="Heading 1"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">H1</button>
            <button type="button" data-action="h2" title="Heading 2"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">H2</button>
            <button type="button" data-action="h3" title="Heading 3"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">H3</button>
            
            <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
            
            <!-- Lists -->
            <button type="button" data-action="bullet-list" title="Bullet List"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">•</button>
            <button type="button" data-action="ordered-list" title="Numbered List"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">1.</button>
            
            <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
            
            <!-- Blocks -->
            <button type="button" data-action="blockquote" title="Blockquote"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">"</button>
            <button type="button" data-action="code-block" title="Code Block"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">{ }</button>
            
            <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
            
            <!-- History -->
            <button type="button" data-action="undo" title="Undo (Ctrl+Z)"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">↶</button>
            <button type="button" data-action="redo" title="Redo (Ctrl+Y)"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 36px;">↷</button>
            
            <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
            
            <!-- Source View -->
            <button type="button" data-action="source" title="View/Edit HTML Source" class="source-toggle"
                    style="padding: 0.375rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; cursor: pointer; min-width: 50px;">
                <span>HTML</span>
            </button>
        </div>
        
        <!-- Editor -->
        <div id="{{ $editorId }}-editor" class="min-h-[300px] max-h-[500px] overflow-y-auto p-4 bg-white prose prose-sm max-w-none focus:outline-none">
            {!! old($editorId, $editorValue) !!}
        </div>
        
        <!-- Source View Textarea (hidden by default) -->
        <textarea id="{{ $editorId }}-source" 
                  class="hidden w-full min-h-[300px] max-h-[500px] p-3 border-0 border-t border-gray-300 font-mono text-sm bg-gray-50 focus:outline-none"
                  placeholder="HTML source code...">
        </textarea>
    </div>
    
    <!-- Hidden textarea for form submission -->
    <textarea name="{{ $name }}" 
              id="{{ $editorId }}" 
              class="hidden" 
              @if($editorRequired) required @endif>{!! old($name, $editorValue) !!}
    </textarea>
    
    <!-- Link Modal -->
    <div id="{{ $editorId }}-link-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Insert/Edit Link</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                        <input type="text" id="{{ $editorId }}-link-url" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Text (optional)</label>
                        <input type="text" id="{{ $editorId }}-link-text" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Click here">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Open in</label>
                        <select id="{{ $editorId }}-link-target" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="_blank">New tab</option>
                            <option value="_self">Same tab</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" id="{{ $editorId }}-link-cancel" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="button" id="{{ $editorId }}-link-remove" 
                            class="px-4 py-2 text-sm font-medium text-red-700 bg-red-100 rounded-md hover:bg-red-200">
                        Remove Link
                    </button>
                    <button type="button" id="{{ $editorId }}-link-save" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    @error($editorId)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

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
                
                // Add button hover and active styles
                function setActiveButton(btn) {
                    btn.style.background = '#3b82f6';
                    btn.style.borderColor = '#3b82f6';
                    btn.style.color = 'white';
                    btn.classList.add('is-active');
                }
                
                function setInactiveButton(btn) {
                    btn.style.background = 'white';
                    btn.style.borderColor = '#d1d5db';
                    btn.style.color = '#374151';
                    btn.classList.remove('is-active');
                }
                
                switch(action) {
                    case 'bold':
                        document.execCommand('bold', false, null);
                        if (document.queryCommandState('bold')) {
                            setActiveButton(button);
                        } else {
                            setInactiveButton(button);
                        }
                        break;
                    case 'italic':
                        document.execCommand('italic', false, null);
                        if (document.queryCommandState('italic')) {
                            setActiveButton(button);
                        } else {
                            setInactiveButton(button);
                        }
                        break;
                    case 'code':
                        document.execCommand('formatBlock', false, '<code>');
                        break;
                    case 'h1':
                        document.execCommand('formatBlock', false, '<h1>');
                        // Reset other heading buttons
                        toolbar.querySelectorAll('[data-action^="h"]').forEach(setInactiveButton);
                        setActiveButton(button);
                        break;
                    case 'h2':
                        document.execCommand('formatBlock', false, '<h2>');
                        toolbar.querySelectorAll('[data-action^="h"]').forEach(setInactiveButton);
                        setActiveButton(button);
                        break;
                    case 'h3':
                        document.execCommand('formatBlock', false, '<h3>');
                        toolbar.querySelectorAll('[data-action^="h"]').forEach(setInactiveButton);
                        setActiveButton(button);
                        break;
                    case 'bullet-list':
                        document.execCommand('insertUnorderedList', false, null);
                        if (document.queryCommandState('insertUnorderedList')) {
                            setActiveButton(button);
                        } else {
                            setInactiveButton(button);
                        }
                        break;
                    case 'ordered-list':
                        document.execCommand('insertOrderedList', false, null);
                        if (document.queryCommandState('insertOrderedList')) {
                            setActiveButton(button);
                        } else {
                            setInactiveButton(button);
                        }
                        break;
                    case 'blockquote':
                        document.execCommand('formatBlock', false, '<blockquote>');
                        setActiveButton(button);
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
            sourceButton.style.background = '#059669';
            sourceButton.style.borderColor = '#059669';
            sourceButton.style.color = 'white';
            sourceButton.classList.add('is-active');
            sourceElement.focus();
        } else {
            // Switch to visual mode
            sourceElement.classList.add('hidden');
            editorElement.innerHTML = sourceElement.value;
            editorElement.style.display = 'block';
            sourceButton.style.background = 'white';
            sourceButton.style.borderColor = '#d1d5db';
            sourceButton.style.color = '#374151';
            sourceButton.classList.remove('is-active');
            editorElement.focus();
            hiddenTextarea.value = editorElement.innerHTML;
        }
    }
});
</script>