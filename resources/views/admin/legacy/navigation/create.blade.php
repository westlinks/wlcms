<x-admin-layout title="Create Navigation Item">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Legacy Navigation Item') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('wlcms.admin.legacy.index') }}" class="hover:text-gray-700">Legacy Integration</a>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
            </svg>
            <a href="{{ route('wlcms.admin.legacy.navigation.index') }}" class="hover:text-gray-700">Navigation Management</a>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
            </svg>
            <span>Create</span>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form method="POST" action="{{ route('wlcms.admin.legacy.navigation.store') }}" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Basic Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       value="{{ old('title') }}"
                                       required 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">
                                    Slug
                                </label>
                                <input type="text" 
                                       name="slug" 
                                       id="slug" 
                                       value="{{ old('slug') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Auto-generated from title if left empty</p>
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Hierarchy -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Hierarchy</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="parent_id" class="block text-sm font-medium text-gray-700">
                                    Parent Navigation Item
                                </label>
                                <select name="parent_id" 
                                        id="parent_id" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">-- Root Level --</option>
                                    @foreach($parentOptions as $option)
                                        <option value="{{ $option->id }}" {{ old('parent_id') == $option->id ? 'selected' : '' }}>
                                            {{ str_repeat('— ', $option->depth ?? 0) }}{{ $option->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">
                                    Sort Order
                                </label>
                                <input type="number" 
                                       name="sort_order" 
                                       id="sort_order" 
                                       value="{{ old('sort_order', 0) }}"
                                       min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                                @error('sort_order')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- URLs and Linking -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">URLs and Linking</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="legacy_url" class="block text-sm font-medium text-gray-700">
                                    Legacy URL <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="legacy_url" 
                                       id="legacy_url" 
                                       value="{{ old('legacy_url') }}"
                                       placeholder="/old-page.html"
                                       required 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">The original URL from the legacy system</p>
                                @error('legacy_url')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cms_url" class="block text-sm font-medium text-gray-700">
                                    CMS URL
                                </label>
                                <input type="text" 
                                       name="cms_url" 
                                       id="cms_url" 
                                       value="{{ old('cms_url') }}"
                                       placeholder="/new-page"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">New URL in the CMS (auto-generated if empty)</p>
                                @error('cms_url')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="target_type" class="block text-sm font-medium text-gray-700">
                                    Target Type
                                </label>
                                <select name="target_type" 
                                        id="target_type" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">-- Select Target Type --</option>
                                    <option value="content" {{ old('target_type') == 'content' ? 'selected' : '' }}>Content Item</option>
                                    <option value="category" {{ old('target_type') == 'category' ? 'selected' : '' }}>Category</option>
                                    <option value="external" {{ old('target_type') == 'external' ? 'selected' : '' }}>External URL</option>
                                    <option value="redirect" {{ old('target_type') == 'redirect' ? 'selected' : '' }}>Redirect</option>
                                </select>
                                @error('target_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="target_id" class="block text-sm font-medium text-gray-700">
                                    Target ID
                                </label>
                                <input type="text" 
                                       name="target_id" 
                                       id="target_id" 
                                       value="{{ old('target_id') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">ID of the target content/category (if applicable)</p>
                                @error('target_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Legacy Integration -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Legacy Integration</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="legacy_item_id" class="block text-sm font-medium text-gray-700">
                                    Legacy Item ID
                                </label>
                                <input type="text" 
                                       name="legacy_item_id" 
                                       id="legacy_item_id" 
                                       value="{{ old('legacy_item_id') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">ID from the legacy navigation system</p>
                                @error('legacy_item_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="legacy_parent_id" class="block text-sm font-medium text-gray-700">
                                    Legacy Parent ID
                                </label>
                                <input type="text" 
                                       name="legacy_parent_id" 
                                       id="legacy_parent_id" 
                                       value="{{ old('legacy_parent_id') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('legacy_parent_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="legacy_depth" class="block text-sm font-medium text-gray-700">
                                    Legacy Depth
                                </label>
                                <input type="number" 
                                       name="legacy_depth" 
                                       id="legacy_depth" 
                                       value="{{ old('legacy_depth', 0) }}"
                                       min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('legacy_depth')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Display Options -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Display Options</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="css_classes" class="block text-sm font-medium text-gray-700">
                                    CSS Classes
                                </label>
                                <input type="text" 
                                       name="css_classes" 
                                       id="css_classes" 
                                       value="{{ old('css_classes') }}"
                                       placeholder="menu-item highlight"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Space-separated CSS classes</p>
                                @error('css_classes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700">
                                    Icon
                                </label>
                                <input type="text" 
                                       name="icon" 
                                       id="icon" 
                                       value="{{ old('icon') }}"
                                       placeholder="fas fa-home"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Icon class (FontAwesome, etc.)</p>
                                @error('icon')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Active
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="visible_in_menu" 
                                       id="visible_in_menu" 
                                       value="1"
                                       {{ old('visible_in_menu', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="visible_in_menu" class="ml-2 block text-sm text-gray-900">
                                    Visible in Menu
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="require_auth" 
                                       id="require_auth" 
                                       value="1"
                                       {{ old('require_auth') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="require_auth" class="ml-2 block text-sm text-gray-900">
                                    Require Authentication
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Metadata</h3>
                        
                        <div>
                            <label for="metadata" class="block text-sm font-medium text-gray-700">
                                Additional Metadata (JSON)
                            </label>
                            <textarea name="metadata" 
                                      id="metadata" 
                                      rows="4"
                                      placeholder='{"custom_field": "value", "legacy_attributes": {}}'
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono text-sm">{{ old('metadata') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Valid JSON format for additional data</p>
                            @error('metadata')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('wlcms.admin.legacy.navigation.index') }}" 
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        
                        <div class="flex space-x-3">
                            <button type="submit" 
                                    name="action" 
                                    value="save"
                                    class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Navigation Item
                            </button>
                            
                            <button type="submit" 
                                    name="action" 
                                    value="save_and_add"
                                    class="bg-green-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Create & Add Another
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const slug = document.getElementById('slug');
            if (!slug.value) {
                slug.value = this.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        // Validate JSON metadata
        document.getElementById('metadata').addEventListener('blur', function() {
            if (this.value.trim()) {
                try {
                    JSON.parse(this.value);
                    this.classList.remove('border-red-300');
                    this.classList.add('border-gray-300');
                } catch (e) {
                    this.classList.remove('border-gray-300');
                    this.classList.add('border-red-300');
                }
            }
        });

        // Auto-generate CMS URL from legacy URL
        document.getElementById('legacy_url').addEventListener('blur', function() {
            const cmsUrl = document.getElementById('cms_url');
            if (!cmsUrl.value && this.value) {
                // Simple transformation: remove file extensions and clean up
                let newUrl = this.value
                    .replace(/\.(html?|php|asp|aspx)$/i, '')
                    .replace(/[^a-zA-Z0-9\/\-_]/g, '-')
                    .replace(/\/+/g, '/')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
                
                if (!newUrl.startsWith('/')) {
                    newUrl = '/' + newUrl;
                }
                
                cmsUrl.value = newUrl;
            }
        });
    </script>
</x-admin-layout>