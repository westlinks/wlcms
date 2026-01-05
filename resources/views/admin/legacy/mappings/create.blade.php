<x-admin-layout title="Create Article Mapping">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Article Mapping') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('wlcms.admin.legacy.index') }}" class="hover:text-gray-700">Legacy Integration</a>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
            </svg>
            <a href="{{ route('wlcms.admin.legacy.mappings.index') }}" class="hover:text-gray-700">Article Mappings</a>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
            </svg>
            <span>Create</span>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('wlcms.admin.legacy.mappings.store') }}" class="space-y-6 p-6">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Legacy Article Selection -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Legacy Article</h3>
                        
                        <div>
                            <label for="legacy_article_id" class="block text-sm font-medium text-gray-700">
                                Select Legacy Article
                            </label>
                            <select name="legacy_article_id" 
                                    id="legacy_article_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>
                                <option value="">Choose a legacy article...</option>
                                @foreach($legacyArticles as $article)
                                <option value="{{ $article->id }}" {{ old('legacy_article_id') == $article->id ? 'selected' : '' }}>
                                    #{{ $article->id }} - {{ $article->title ?? $article->menu_title ?? 'Untitled' }}
                                    @if($article->published ?? true)
                                        <span class="text-green-600">(Published)</span>
                                    @else
                                        <span class="text-gray-500">(Draft)</span>
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('legacy_article_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            @if($legacyArticles->isEmpty())
                                <p class="mt-2 text-sm text-gray-500">
                                    No unmapped legacy articles found. All legacy articles may already be mapped to CMS content.
                                </p>
                            @endif
                        </div>
                        
                        <!-- Legacy Article Preview -->
                        <div id="legacy-preview" class="hidden bg-gray-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-gray-900">Article Preview</h4>
                            <div id="legacy-preview-content" class="mt-2 text-sm text-gray-600">
                                <!-- Dynamic content will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- CMS Content Selection -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">CMS Content</h3>
                        
                        <div>
                            <label for="cms_content_id" class="block text-sm font-medium text-gray-700">
                                Map to CMS Content
                            </label>
                            <select name="cms_content_id" 
                                    id="cms_content_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    required>
                                <option value="">Choose CMS content...</option>
                                @foreach($cmsContent as $content)
                                <option value="{{ $content->id }}" {{ old('cms_content_id') == $content->id ? 'selected' : '' }}>
                                    {{ $content->title }} 
                                    <span class="text-sm text-gray-500">({{ ucfirst($content->status) }} - {{ ucfirst($content->type) }})</span>
                                </option>
                                @endforeach
                            </select>
                            @error('cms_content_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <div class="mt-2">
                                <a href="{{ route('wlcms.admin.content.create') }}" 
                                   class="text-sm text-blue-600 hover:text-blue-500"
                                   target="_blank">
                                    + Create new CMS content
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sync Configuration -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sync Configuration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="sync_direction" class="block text-sm font-medium text-gray-700">
                                Sync Direction
                            </label>
                            <select name="sync_direction" 
                                    id="sync_direction" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="legacy_to_cms" {{ old('sync_direction') === 'legacy_to_cms' ? 'selected' : '' }}>
                                    Legacy → CMS
                                </option>
                                <option value="cms_to_legacy" {{ old('sync_direction') === 'cms_to_legacy' ? 'selected' : '' }}>
                                    CMS → Legacy
                                </option>
                                <option value="bidirectional" {{ old('sync_direction') === 'bidirectional' ? 'selected' : '' }}>
                                    Bidirectional
                                </option>
                            </select>
                            @error('sync_direction')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="preserve_legacy_urls" 
                                       value="1" 
                                       {{ old('preserve_legacy_urls') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Preserve Legacy URLs</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Field Mappings -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Field Mappings</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-sm text-gray-600 mb-4">
                            Configure custom field mappings. Leave empty to use default configuration mappings.
                        </p>
                        
                        <div id="field-mappings" class="space-y-3">
                            <!-- Dynamic field mappings will be added here -->
                            @php
                                $defaultMappings = config('wlcms.legacy.field_mappings', []);
                                $oldMappings = old('field_mappings', []);
                            @endphp
                            
                            @foreach($defaultMappings as $legacyField => $cmsField)
                            <div class="grid grid-cols-3 gap-4 items-center">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $legacyField)) }}</label>
                                </div>
                                <div class="text-sm text-gray-500">→</div>
                                <div>
                                    <input type="text" 
                                           name="field_mappings[{{ $legacyField }}]" 
                                           value="{{ $oldMappings[$legacyField] ?? '' }}"
                                           placeholder="Override: {{ $cmsField }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t">
                    <a href="{{ route('wlcms.admin.legacy.mappings.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const legacySelect = document.getElementById('legacy_article_id');
        const previewDiv = document.getElementById('legacy-preview');
        const previewContent = document.getElementById('legacy-preview-content');
        
        legacySelect?.addEventListener('change', function() {
            if (this.value) {
                // Show loading state
                previewDiv.classList.remove('hidden');
                previewContent.innerHTML = '<div class="text-gray-500">Loading article preview...</div>';
                
                // In a real implementation, you would fetch article details via AJAX
                // For now, we'll just show the selected option text
                const selectedOption = this.options[this.selectedIndex];
                previewContent.innerHTML = `
                    <div>
                        <strong>ID:</strong> ${this.value}<br>
                        <strong>Title:</strong> ${selectedOption.textContent.split(' - ')[1] || 'N/A'}
                    </div>
                `;
            } else {
                previewDiv.classList.add('hidden');
            }
        });
    });
    </script>
    @endpush
</x-admin-layout>