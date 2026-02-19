<x-admin-layout title="Create Content">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Create Content") }}
        </h2>
    </x-slot>
    <form method="POST" action="{{ route('wlcms.admin.content.store') }}" enctype="multipart/form-data" 
          onsubmit="return validateZones(event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content Area --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Info Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Content Details</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                          @error('title') border-red-300 @enderror">
                            @error('title')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">URL Slug</label>
                            <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                          @error('slug') border-red-300 @enderror"
                                   placeholder="Auto-generated from title if left empty">
                            @error('slug')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-gray-600 text-sm mt-1">Leave empty to auto-generate from title</p>
                        </div>

                        <div>
                            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                             @error('excerpt') border-red-300 @enderror"
                                      placeholder="Brief description or summary...">{{ old('excerpt') }}</textarea>
                            @error('excerpt')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Content Editor Card (hidden when template selected) --}}
                <div class="bg-white rounded-lg shadow p-6" 
                     x-data="{ hasTemplate: false }"
                     @template-selected.window="hasTemplate = !!$event.detail.template"
                     x-show="!hasTemplate">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Content</h3>
                    
                    @include('wlcms::admin.components.editor', [
                        'name' => 'content',
                        'value' => old('content'),
                        'label' => 'Content',
                        'required' => false
                    ])
                    
                    @error('content')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Template Selection Card --}}
                <div class="bg-white rounded-lg shadow p-6" 
                     x-data="{ 
                         selectedTemplate: null,
                         zoneData: {},
                         updateZone(key, value) {
                             this.zoneData[key] = value;
                         }
                     }"
                     @template-selected.window="
                         selectedTemplate = $event.detail.template;
                         // Initialize zone data object with keys for each zone
                         if (selectedTemplate && selectedTemplate.zones) {
                             Object.keys(selectedTemplate.zones).forEach(key => {
                                 if (!zoneData[key]) {
                                     zoneData[key] = '';
                                 }
                             });
                         }
                     "
                     @updateZone="updateZone($event.detail.key, $event.detail.value)">

                    @include('wlcms::admin.components.template-picker', [
                        'name' => 'template_identifier',
                        'selected' => old('template_identifier'),
                        'label' => 'Page Template'
                    ])

                    {{-- Zone Editor Section - Shows when template with zones is selected --}}
                    <div x-show="selectedTemplate && selectedTemplate.zones && Object.keys(selectedTemplate.zones).length > 0" 
                         x-cloak
                         class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <span class="text-blue-600" x-text="selectedTemplate?.name"></span> Content Zones
                        </h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Fill in the content zones for this template. Required zones are marked with <span class="text-red-500">*</span>
                        </p>
                        <p class="text-sm text-gray-500 mb-6 italic">
                            Note: Basic editors are shown here. After saving, you'll have access to full-featured editors including TipTap rich text, form selectors, and media galleries in the edit view.
                        </p>

                        {{-- Dynamic Zone Rendering with Alpine.js --}}
                        <div class="space-y-6" id="zones-container">
                            <template x-for="(zoneConfig, zoneKey) in selectedTemplate?.zones || {}" :key="zoneKey">
                                <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
                                    <h4 class="text-md font-semibold mb-3 flex items-center justify-between">
                                        <span>
                                            <span x-text="zoneConfig.label || zoneKey"></span>
                                            <span x-show="zoneConfig.required" class="text-red-500"> *</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded" x-text="zoneConfig.type"></span>
                                    </h4>
                                    
                                    {{-- Full-featured rich text editor for rich_text zones --}}
                                    <div x-show="zoneConfig.type === 'rich_text'" class="zone-rich-text">
                                        <div class="editor-container" style="border: 1px solid #d1d5db; border-radius: 0.375rem; overflow: hidden; background: white;">
                                            {{-- Full toolbar with all features --}}
                                            <div class="editor-toolbar" :data-zone="zoneKey" style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem; border-bottom: 1px solid #d1d5db; background: #f9fafb;">
                                                <button type="button" data-action="bold" title="Bold (Ctrl+B)" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm font-semibold hover:bg-gray-50 min-w-[36px]">B</button>
                                                <button type="button" data-action="italic" title="Italic (Ctrl+I)" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm italic hover:bg-gray-50 min-w-[36px]">I</button>
                                                <button type="button" data-action="code" title="Inline Code" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">&lt;/&gt;</button>
                                                
                                                <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
                                                
                                                <button type="button" data-action="h1" title="Heading 1" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">H1</button>
                                                <button type="button" data-action="h2" title="Heading 2" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">H2</button>
                                                <button type="button" data-action="h3" title="Heading 3" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">H3</button>
                                                
                                                <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
                                                
                                                <button type="button" data-action="bullet-list" title="Bullet List" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">•</button>
                                                <button type="button" data-action="ordered-list" title="Numbered List" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">1.</button>
                                                
                                                <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
                                                
                                                <button type="button" data-action="blockquote" title="Blockquote" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">"</button>
                                                <button type="button" data-action="code-block" title="Code Block" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">{ }</button>
                                                
                                                <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
                                                
                                                <button type="button" data-action="undo" title="Undo (Ctrl+Z)" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">↶</button>
                                                <button type="button" data-action="redo" title="Redo (Ctrl+Shift+Z)" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50 min-w-[36px]">↷</button>
                                                
                                                <div class="separator" style="width: 1px; background: #d1d5db; margin: 0.25rem 0;"></div>
                                                
                                                <button type="button" data-action="source" title="View/Edit HTML Source" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50">HTML</button>
                                            </div>
                                            
                                            {{-- Visual editor --}}
                                            <div 
                                                contenteditable="true"
                                                :data-zone="zoneKey"
                                                class="zone-editor prose max-w-none p-4 min-h-[200px] focus:outline-none"
                                                style="background: white;"
                                                @input="zoneData[zoneKey] = $el.innerHTML"
                                                x-init="$el.innerHTML = zoneData[zoneKey] || ''"></div>
                                            
                                            {{-- HTML Source view (hidden by default) --}}
                                            <textarea 
                                                :data-zone="zoneKey"
                                                class="zone-source hidden w-full min-h-[200px] p-3 border-0 border-t border-gray-300 font-mono text-sm bg-gray-50 focus:outline-none"
                                                @input="zoneData[zoneKey] = $el.value"></textarea>
                                        </div>
                                    </div>
                                    
                                    {{-- Plain textarea for other zone types --}}
                                    <div x-show="zoneConfig.type !== 'rich_text'">
                                        <textarea 
                                            x-model="zoneData[zoneKey]"
                                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                            rows="4"
                                            :placeholder="'Enter content for ' + (zoneConfig.label || zoneKey)"
                                            :required="zoneConfig.required"></textarea>
                                        <p class="text-xs text-gray-500 mt-1" x-show="zoneConfig.type === 'repeater'">JSON array format. Full repeater controls available after saving.</p>
                                        <p class="text-xs text-gray-500 mt-1" x-show="zoneConfig.type === 'form_embed'">Enter form shortcode like [form id="contact"]. Full form selector available after saving.</p>
                                        <p class="text-xs text-gray-500 mt-1" x-show="zoneConfig.type === 'media_gallery'">Upload images after saving. Enter image URLs for now.</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        {{-- Rich text editor toolbar handler script --}}
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Setup toolbar handlers for dynamically created editors
                            const container = document.getElementById('zones-container');
                            if (!container) return;
                            
                            // Use event delegation for toolbar buttons
                            container.addEventListener('click', function(e) {
                                const button = e.target.closest('[data-action]');
                                if (!button) return;
                                
                                e.preventDefault();
                                const action = button.getAttribute('data-action');
                                const toolbar = button.closest('.editor-toolbar');
                                const zoneKey = toolbar.getAttribute('data-zone');
                                const editorDiv = toolbar.nextElementSibling;
                                const sourceTextarea = editorDiv.nextElementSibling;
                                
                                // Focus the appropriate editor
                                if (sourceTextarea && !sourceTextarea.classList.contains('hidden')) {
                                    sourceTextarea.focus();
                                } else {
                                    editorDiv.focus();
                                }
                                
                                switch(action) {
                                    case 'bold':
                                        document.execCommand('bold', false, null);
                                        toggleActiveClass(button, 'bold');
                                        break;
                                    case 'italic':
                                        document.execCommand('italic', false, null);
                                        toggleActiveClass(button, 'italic');
                                        break;
                                    case 'code':
                                        const selection = window.getSelection();
                                        if (selection.toString()) {
                                            document.execCommand('insertHTML', false, '<code>' + selection.toString() + '</code>');
                                        }
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
                                        toggleActiveClass(button, 'insertUnorderedList');
                                        break;
                                    case 'ordered-list':
                                        document.execCommand('insertOrderedList', false, null);
                                        toggleActiveClass(button, 'insertOrderedList');
                                        break;
                                    case 'blockquote':
                                        document.execCommand('formatBlock', false, '<blockquote>');
                                        break;
                                    case 'code-block':
                                        document.execCommand('formatBlock', false, '<pre>');
                                        break;
                                    case 'undo':
                                        document.execCommand('undo', false, null);
                                        break;
                                    case 'redo':
                                        document.execCommand('redo', false, null);
                                        break;
                                    case 'source':
                                        toggleSourceView(editorDiv, sourceTextarea, button);
                                        break;
                                }
                                
                                // Trigger input event to update Alpine.js data
                                if (sourceTextarea.classList.contains('hidden')) {
                                    editorDiv.dispatchEvent(new Event('input'));
                                } else {
                                    sourceTextarea.dispatchEvent(new Event('input'));
                                }
                            });
                            
                            function toggleActiveClass(button, command) {
                                if (document.queryCommandState(command)) {
                                    button.classList.add('bg-blue-500', 'text-white', 'border-blue-500');
                                    button.classList.remove('bg-white', 'border-gray-300');
                                } else {
                                    button.classList.remove('bg-blue-500', 'text-white', 'border-blue-500');
                                    button.classList.add('bg-white', 'border-gray-300');
                                }
                            }
                            
                            function toggleSourceView(editorDiv, sourceTextarea, button) {
                                if (sourceTextarea.classList.contains('hidden')) {
                                    // Switch to source mode
                                    sourceTextarea.value = editorDiv.innerHTML;
                                    sourceTextarea.classList.remove('hidden');
                                    editorDiv.style.display = 'none';
                                    button.classList.add('bg-green-600', 'text-white', 'border-green-600');
                                    button.classList.remove('bg-white', 'border-gray-300');
                                    sourceTextarea.focus();
                                } else {
                                    // Switch to visual mode
                                    editorDiv.innerHTML = sourceTextarea.value;
                                    sourceTextarea.classList.add('hidden');
                                    editorDiv.style.display = 'block';
                                    button.classList.remove('bg-green-600', 'text-white', 'border-green-600');
                                    button.classList.add('bg-white', 'border-gray-300');
                                    editorDiv.focus();
                                    editorDiv.dispatchEvent(new Event('input'));
                                }
                            }
                        });
                        </script>

                        {{-- Hidden input to store all zone data as JSON --}}
                        <input type="hidden" name="zones_json" :value="JSON.stringify(zoneData)">
                        
                        {{-- Template Settings Panel --}}
                        @include('wlcms::admin.components.template-settings-panel', [
                            'settings' => old('settings', [])
                        ])
                    </div>
                </div>

                {{-- SEO Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Leave empty to use the main title"
                                   maxlength="60">
                            <p class="text-gray-600 text-xs mt-1">Recommended: 50-60 characters</p>
                        </div>

                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description for search engines..."
                                      maxlength="160">{{ old('meta_description') }}</textarea>
                            <p class="text-gray-600 text-xs mt-1">Recommended: 150-160 characters</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Publish Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Publish</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" name="status"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select id="type" name="type"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="page" {{ old('type') === 'page' ? 'selected' : '' }}>Page</option>
                                <option value="post" {{ old('type') === 'post' ? 'selected' : '' }}>Post</option>
                                <option value="article" {{ old('type') === 'article' ? 'selected' : '' }}>Article</option>
                            </select>
                        </div>

                        <div>
                            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                            <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-gray-600 text-xs mt-1">Leave empty to publish immediately</p>
                        </div>
                    </div>

                    <div class="flex justify-between pt-4 mt-6 border-t">
                        <a href="{{ route('wlcms.admin.content.index') }}"
                           class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Create Content
                        </button>
                    </div>
                </div>

                {{-- Featured Image Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Featured Image</h3>
                    
                    <div class="space-y-4">
                        {{-- Hidden input to store selected media ID --}}
                        <input type="hidden" id="featured_media_id" name="featured_media_id" value="">
                        
                        {{-- Featured image preview --}}
                        <div id="featured-image-preview" class="hidden">
                            <div class="relative group">
                                <img id="featured-image-thumbnail" src="" alt="" class="w-full h-48 object-cover rounded-lg">
                                <button type="button" 
                                        onclick="removeFeaturedImage()"
                                        class="absolute top-2 right-2 bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Remove
                                </button>
                                <div class="mt-2">
                                    <p id="featured-image-name" class="text-sm text-gray-600"></p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Select button --}}
                        <div id="featured-image-select">
                            <button type="button" 
                                    onclick="openFeaturedImagePicker()"
                                    class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors">
                                📷 Select Featured Image
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Navigation Settings Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Navigation</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="show_in_menu" name="show_in_menu" value="1" 
                                   {{ old('show_in_menu') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="show_in_menu" class="ml-2 block text-sm font-medium text-gray-700">
                                Show in Navigation Menu
                            </label>
                        </div>

                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">Parent Menu Item</label>
                            <select id="parent_id" name="parent_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">None (Top Level)</option>
                                @foreach($potentialParents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->menu_title ?: $parent->title }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-gray-600 text-xs mt-1">Select a parent for nested menu structure</p>
                        </div>

                        <div>
                            <label for="menu_title" class="block text-sm font-medium text-gray-700 mb-2">Menu Title</label>
                            <input type="text" id="menu_title" name="menu_title" value="{{ old('menu_title') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Leave empty to use page title">
                            <p class="text-gray-600 text-xs mt-1">Custom title for navigation menu</p>
                        </div>

                        <div>
                            <label for="menu_location" class="block text-sm font-medium text-gray-700 mb-2">Menu Location</label>
                            <select id="menu_location" name="menu_location"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="primary" {{ old('menu_location', 'primary') === 'primary' ? 'selected' : '' }}>Primary Menu</option>
                                <option value="footer" {{ old('menu_location') === 'footer' ? 'selected' : '' }}>Footer Menu</option>
                                <option value="sidebar" {{ old('menu_location') === 'sidebar' ? 'selected' : '' }}>Sidebar Menu</option>
                            </select>
                        </div>

                        <div>
                            <label for="menu_order" class="block text-sm font-medium text-gray-700 mb-2">Menu Order</label>
                            <input type="number" id="menu_order" name="menu_order" value="{{ old('menu_order', 0) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   min="0">
                            <p class="text-gray-600 text-xs mt-1">Lower numbers appear first</p>
                        </div>
                    </div>
                </div>

                {{-- Categories/Tags Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                            <input type="text" id="tags" name="tags" value="{{ old('tags') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter tags separated by commas">
                            <p class="text-gray-600 text-xs mt-1">Separate multiple tags with commas</p>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   min="0">
                            <p class="text-gray-600 text-xs mt-1">Higher numbers appear first</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    </form>

    {{-- Include Media Picker Modal --}}
    @include('wlcms::admin.components.media-picker')

@push('scripts')
    {{-- Featured Image Picker Script --}}
    <script>
        // Initialize MediaPicker manually if it doesn't exist (embedded mode fix)
        if (!window.mediaPicker && document.getElementById('media-picker-modal') && typeof MediaPicker !== 'undefined') {
            const mediaPickerInstance = new MediaPicker();
            mediaPickerInstance.init();
            window.mediaPicker = mediaPickerInstance;
        }
        
        // Define functions immediately so onclick handlers work
        window.openFeaturedImagePicker = function() {
            // Wait for mediaPicker to be ready (with timeout)
            const checkMediaPicker = (attempts = 0) => {
                if (window.mediaPicker) {
                    window.mediaPicker.open((media) => {
                        // Set hidden input value
                        document.getElementById('featured_media_id').value = media.id;
                        
                        // Update preview
                        document.getElementById('featured-image-thumbnail').src = media.thumbnail_url || media.url;
                        document.getElementById('featured-image-thumbnail').alt = media.alt_text || media.name;
                        document.getElementById('featured-image-name').textContent = media.name;
                        
                        // Show preview, hide select button
                        document.getElementById('featured-image-preview').classList.remove('hidden');
                        document.getElementById('featured-image-select').classList.add('hidden');
                    });
                } else if (attempts < 20) {
                    // Try again in 100ms (max 2 seconds)
                    setTimeout(() => checkMediaPicker(attempts + 1), 100);
                } else {
                    alert('Media picker failed to load. Please refresh the page.');
                }
            };
            checkMediaPicker();
        };

        window.removeFeaturedImage = function() {
            // Clear hidden input
            document.getElementById('featured_media_id').value = '';
            
            // Hide preview, show select button
            document.getElementById('featured-image-preview').classList.add('hidden');
            document.getElementById('featured-image-select').classList.remove('hidden');
        };
        
        // Validate required zones before form submission
        window.validateZones = function(event) {
            const zonesInput = document.querySelector('input[name="zones_json"]');
            if (!zonesInput || !zonesInput.value) return true;
            
            try {
                const zones = JSON.parse(zonesInput.value);
                const templateZonesContainer = document.getElementById('template-zones-container');
                
                // Get Alpine component data
                const alpineData = Alpine.$data(templateZonesContainer?.closest('[x-data]'));
                if (!alpineData || !alpineData.selectedTemplate) return true;
                
                // Check each required zone
                const requiredZones = Object.entries(alpineData.selectedTemplate.zones || {})
                    .filter(([key, config]) => config.required)
                    .map(([key]) => key);
                
                const missingZones = requiredZones.filter(key => {
                    const value = zones[key];
                    return !value || value.trim() === '' || value === '<p></p>' || value === '<br>';
                });
                
                if (missingZones.length > 0) {
                    const zoneLabels = missingZones.map(key => {
                        const config = alpineData.selectedTemplate.zones[key];
                        return config.label || key;
                    }).join(', ');
                    
                    alert('Please fill in all required zones: ' + zoneLabels);
                    event.preventDefault();
                    return false;
                }
                
                return true;
            } catch (e) {
                console.error('Zone validation error:', e);
                return true; // Allow submission if validation fails
            }
        };
    </script>
@endpush
</x-admin-layout>
