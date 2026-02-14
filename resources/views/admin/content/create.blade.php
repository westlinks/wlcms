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

                {{-- Content Editor Card --}}
                <div class="bg-white rounded-lg shadow p-6">
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
                         zoneData: {}
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
                     ">

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

                        {{-- Dynamic Zone Rendering --}}
                        <div class="space-y-6" id="template-zones-container">
                            <template x-for="(zoneConfig, zoneKey) in selectedTemplate?.zones || {}" :key="zoneKey">
                                <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
                                    <h4 class="text-md font-semibold mb-3">
                                        <span x-text="zoneConfig.label || zoneKey"></span>
                                        <span x-show="zoneConfig.required" class="text-red-500"> *</span>
                                        <span class="text-xs font-normal text-gray-500 ml-2" x-text="'(' + zoneConfig.type + ')'"></span>
                                    </h4>
                                    
                                    {{-- Rich text editor for rich_text zones --}}
                                    <div x-show="zoneConfig.type === 'rich_text'">
                                        <div 
                                            contenteditable="true"
                                            @input="zoneData[zoneKey] = $el.innerHTML"
                                            x-init="$el.innerHTML = zoneData[zoneKey] || ''"
                                            class="prose max-w-none p-4 min-h-[200px] focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            style="border: 1px solid #d1d5db; border-radius: 0.375rem; background: white;"
                                            :placeholder="'Enter content for ' + (zoneConfig.label || zoneKey)"></div>
                                        <p x-show="zoneConfig.required && (!zoneData[zoneKey] || zoneData[zoneKey].trim() === '' || zoneData[zoneKey] === '<br>' || zoneData[zoneKey] === '<p></p>')" class="text-xs text-red-600 mt-1">This field is required</p>
                                    </div>
                                    
                                    {{-- Plain textarea for other zone types --}}
                                    <div x-show="zoneConfig.type !== 'rich_text'">
                                        <textarea 
                                            x-model="zoneData[zoneKey]"
                                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                            rows="4"
                                            :placeholder="'Enter content for ' + (zoneConfig.label || zoneKey)"></textarea>
                                        <p x-show="zoneConfig.required && !zoneData[zoneKey]" class="text-xs text-red-600 mt-1">This field is required</p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Hidden input to store all zone data as JSON --}}
                        <input type="hidden" name="zones_json" :value="JSON.stringify(zoneData)">
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
