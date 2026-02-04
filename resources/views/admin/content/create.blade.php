<x-admin-layout title="Create Content">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Content') }}
        </h2>
        
        {{-- WLCMS Assets --}}
        @vite(['resources/vendor/wlcms/css/wlcms.css', 'resources/vendor/wlcms/js/wlcms.js'])
    </x-slot>
    <form method="POST" action="{{ route('wlcms.admin.content.store') }}" enctype="multipart/form-data">
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
                        <div>
                            <input type="file" id="featured_image" name="featured_image" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('featured_image')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="featured_image_alt" class="block text-sm font-medium text-gray-700 mb-2">Alt Text</label>
                            <input type="text" id="featured_image_alt" name="featured_image_alt" value="{{ old('featured_image_alt') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Describe the image for accessibility">
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
</x-admin-layout>