<x-admin-layout title="Edit Content">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Content') }}
        </h2>
        
        {{-- WLCMS Assets --}}
        @vite(['resources/vendor/wlcms/css/wlcms.css', 'resources/vendor/wlcms/js/wlcms.js'])
    </x-slot>
    <form method="POST" action="{{ route('wlcms.admin.content.update', $content) }}" enctype="multipart/form-data">
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
                        
                        {{-- Hidden input to store selected media ID --}}
                        <input type="hidden" id="featured_media_id" name="featured_media_id" 
                               value="{{ $content->mediaAssets()->wherePivot('type', 'featured')->first()?->id }}">
                        
                        @php
                            $featuredMedia = $content->mediaAssets()->wherePivot('type', 'featured')->first();
                        @endphp
                        
                        {{-- Featured image preview --}}
                        <div id="featured-image-preview" class="{{ $featuredMedia ? '' : 'hidden' }}">
                            <div class="relative group">
                                <img id="featured-image-thumbnail" 
                                     src="{{ $featuredMedia?->thumbnails['medium'] ?? $featuredMedia?->path }}" 
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
@endsection

@push('scripts')
    {{-- Featured Image Picker Script --}}
    <script>
        // Wait for WLCMS to initialize
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Featured image picker script loaded');
            console.log('window.mediaPicker:', window.mediaPicker);
            
            // Make functions globally available
            window.openFeaturedImagePicker = function() {
                if (window.mediaPicker) {
                    window.mediaPicker.open((media) => {
                        // Set hidden input value
                        document.getElementById('featured_media_id').value = media.id;
                        
                        // Update preview
                        document.getElementById('featured-image-thumbnail').src = media.thumbnail || media.url;
                        document.getElementById('featured-image-thumbnail').alt = media.name;
                        document.getElementById('featured-image-name').textContent = media.name;
                        
                        // Show preview, hide select button
                        document.getElementById('featured-image-preview').classList.remove('hidden');
                        document.getElementById('featured-image-select').classList.add('hidden');
                    });
                } else {
                    console.error('Media picker not initialized');
                }
            };

            window.removeFeaturedImage = function() {
                // Clear hidden input
                document.getElementById('featured_media_id').value = '';
                
                // Hide preview, show select button
                document.getElementById('featured-image-preview').classList.add('hidden');
                document.getElementById('featured-image-select').classList.remove('hidden');
            };
        });
    </script>
@endpush
</x-admin-layout>
