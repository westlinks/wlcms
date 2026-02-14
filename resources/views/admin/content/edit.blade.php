<x-admin-layout title="Edit Content">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Content') }}
        </h2>
    </x-slot>
    <form method="POST" action="{{ route('wlcms.admin.content.update', $content) }}" enctype="multipart/form-data"
          onsubmit="return validateZones(event)">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3 space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" required
                               value="{{ old('title', $content->title) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div>
                        @include('wlcms::admin.components.editor', [
                            'name' => 'content',
                            'value' => old('content', $content->content),
                            'label' => 'Content',
                            'required' => false
                        ])
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Brief description or excerpt...">{{ old('excerpt', $content->excerpt) }}</textarea>
                        @error('excerpt')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Template Selection -->
<div class="bg-gray-50 rounded-lg p-4"
                         x-data="{ 
                             selectedTemplate: null,
                             zoneData: @js(old('zones_json') ? json_decode(old('zones_json'), true) : ($content->templateSettings ? $content->templateSettings->getAllZonesData() : (object)[]))
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
                            'selected' => old('template_identifier', $content->template),
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
                </div>

                <div class="space-y-6">
                    <!-- Publish Settings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Publish Settings</h3>
                        
                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="draft" {{ old('status', $content->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $content->status) === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="scheduled" {{ old('status', $content->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="archived" {{ old('status', $content->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Content Type</label>
                                <select name="type" id="type" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="page" {{ old('type', $content->type) === 'page' ? 'selected' : '' }}>Page</option>
                                    <option value="post" {{ old('type', $content->type) === 'post' ? 'selected' : '' }}>Post</option>
                                    <option value="article" {{ old('type', $content->type) === 'article' ? 'selected' : '' }}>Article</option>
                                    <option value="news" {{ old('type', $content->type) === 'news' ? 'selected' : '' }}>News</option>
                                    <option value="event" {{ old('type', $content->type) === 'event' ? 'selected' : '' }}>Event</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t space-y-3">
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                💾 Update Content
                            </button>
                            <a href="{{ route('wlcms.admin.content.show', $content) }}" 
                               class="block w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium text-center">
                                Cancel
                            </a>
                        </div>
                    </div>

                    <!-- Navigation Settings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Navigation Settings</h3>
                        
                        <div class="space-y-4">
                            <!-- Show in Menu -->
                            <div class="flex items-center">
                                <input type="checkbox" id="show_in_menu" name="show_in_menu" value="1" 
                                       {{ old('show_in_menu', $content->show_in_menu) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="show_in_menu" class="ml-2 block text-sm font-medium text-gray-700">
                                    Show in Navigation Menu
                                </label>
                            </div>

                            <!-- Menu Title -->
                            <div>
                                <label for="menu_title" class="block text-sm font-medium text-gray-700">Menu Title</label>
                                <input type="text" id="menu_title" name="menu_title" 
                                       value="{{ old('menu_title', $content->menu_title) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Leave empty to use page title">
                                <p class="text-gray-600 text-xs mt-1">Custom title for navigation menu</p>
                            </div>

                            <!-- Menu Location -->
                            <div>
                                <label for="menu_location" class="block text-sm font-medium text-gray-700">Menu Location</label>
                                <select id="menu_location" name="menu_location"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="primary" {{ old('menu_location', $content->menu_location) === 'primary' ? 'selected' : '' }}>Primary Menu</option>
                                    <option value="footer" {{ old('menu_location', $content->menu_location) === 'footer' ? 'selected' : '' }}>Footer Menu</option>
                                    <option value="sidebar" {{ old('menu_location', $content->menu_location) === 'sidebar' ? 'selected' : '' }}>Sidebar Menu</option>
                                </select>
                            </div>

                            <!-- Menu Order -->
                            <div>
                                <label for="menu_order" class="block text-sm font-medium text-gray-700">Menu Order</label>
                                <input type="number" id="menu_order" name="menu_order" 
                                       value="{{ old('menu_order', $content->menu_order) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       min="0">
                                <p class="text-gray-600 text-xs mt-1">Lower numbers appear first</p>
                            </div>
                        </div>
                    </div>
                    <!-- Featured Image -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Featured Image</h3>
                        
                        @php
                            $featuredMedia = $content->mediaAssets->first(function($media) {
                                return $media->pivot->type === 'featured';
                            });
                        @endphp
                        
                        {{-- Hidden input to store selected media ID --}}
                        <input type="hidden" id="featured_media_id" name="featured_media_id" 
                               value="{{ $featuredMedia?->id ?? '' }}">
                        
                        {{-- Featured image preview --}}
                        <div id="featured-image-preview" class="{{ $featuredMedia ? '' : 'hidden' }}">
                            <div class="relative group">
                                <img id="featured-image-thumbnail" 
                                     src="{{ $featuredMedia?->getThumbnailUrl('medium') }}" 
                                     alt="{{ $featuredMedia?->alt_text }}" 
                                     class="w-full h-32 object-cover rounded-lg">
                                <button type="button" 
                                        onclick="removeFeaturedImage()"
                                        class="absolute top-2 right-2 bg-red-600 text-white px-2 py-1 text-xs rounded hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Remove
                                </button>
                                <div class="mt-2">
                                    <p id="featured-image-name" class="text-xs text-gray-600">{{ $featuredMedia?->name }}</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Select button --}}
                        <div id="featured-image-select" class="{{ $featuredMedia ? 'hidden' : '' }}">
                            <button type="button" 
                                    onclick="openFeaturedImagePicker()"
                                    class="w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded text-sm text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors">
                                📷 Select Image
                            </button>
                        </div>
                    </div>
                    <!-- Content Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Content Info</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $content->slug) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       required>
                                <p class="mt-1 text-xs text-gray-500">URL-friendly identifier (lowercase, hyphens only)</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Created:</span>
                                <span class="ml-2 text-gray-900">{{ $content->created_at->format('M j, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="ml-2 text-gray-900">{{ $content->updated_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-2">
                            <a href="{{ route('wlcms.admin.content.preview', $content) }}" 
                               class="block w-full px-3 py-2 text-center text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                👁️ Preview Changes
                            </a>
                            <a href="{{ route('wlcms.admin.content.revisions', $content) }}" 
                               class="block w-full px-3 py-2 text-center text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                📋 View Revisions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
