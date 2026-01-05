<x-admin-layout title="Edit Article Mapping">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Article Mapping') }}
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
            <span>Edit #{{ $mapping->id }}</span>
        </div>

        <!-- Mapping Info -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Legacy Article</h3>
                    <div class="mt-2 p-4 bg-gray-50 rounded-md">
                        <div class="text-sm">
                            <strong>ID:</strong> {{ $mapping->legacy_article_id }}<br>
                            @if($legacyArticle)
                                <strong>Title:</strong> {{ $legacyArticle->title ?? $legacyArticle->menu_title ?? 'Untitled' }}<br>
                                @if(isset($legacyArticle->published))
                                    <strong>Status:</strong> {{ $legacyArticle->published ? 'Published' : 'Draft' }}
                                @endif
                            @else
                                <span class="text-red-600">Legacy article not found</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900">CMS Content</h3>
                    <div class="mt-2 p-4 bg-gray-50 rounded-md">
                        <div class="text-sm">
                            <strong>Title:</strong> {{ $mapping->contentItem->title }}<br>
                            <strong>Type:</strong> {{ ucfirst($mapping->contentItem->type) }}<br>
                            <strong>Status:</strong> {{ ucfirst($mapping->contentItem->status) }}
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('wlcms.admin.content.edit', $mapping->contentItem) }}" 
                               class="text-sm text-blue-600 hover:text-blue-500"
                               target="_blank">
                                Edit CMS Content →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form method="POST" action="{{ route('wlcms.admin.legacy.mappings.update', $mapping) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Sync Configuration -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sync Configuration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="sync_direction" class="block text-sm font-medium text-gray-700">
                                Sync Direction
                            </label>
                            <select name="sync_direction" 
                                    id="sync_direction" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="legacy_to_cms" {{ $mapping->sync_direction === 'legacy_to_cms' ? 'selected' : '' }}>
                                    Legacy → CMS
                                </option>
                                <option value="cms_to_legacy" {{ $mapping->sync_direction === 'cms_to_legacy' ? 'selected' : '' }}>
                                    CMS → Legacy
                                </option>
                                <option value="bidirectional" {{ $mapping->sync_direction === 'bidirectional' ? 'selected' : '' }}>
                                    Bidirectional
                                </option>
                            </select>
                            @error('sync_direction')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Mapping Status
                            </label>
                            <select name="status" 
                                    id="status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="active" {{ $mapping->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $mapping->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="error" {{ $mapping->status === 'error' ? 'selected' : '' }}>Error</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="auto_sync" 
                                       value="1" 
                                       {{ $mapping->auto_sync ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Auto Sync</span>
                            </label>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="preserve_legacy_urls" 
                                       value="1" 
                                       {{ $mapping->preserve_legacy_urls ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Preserve Legacy URLs</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Field Overrides -->
                <div class="border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Field Overrides</h3>
                        <button type="button" 
                                id="add-override" 
                                class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                            + Add Override
                        </button>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div id="field-overrides" class="space-y-3">
                            @foreach($mapping->fieldOverrides as $override)
                            <div class="grid grid-cols-4 gap-4 items-center override-row">
                                <div>
                                    <input type="text" 
                                           name="existing_overrides[{{ $loop->index }}][field_name]" 
                                           value="{{ $override->field_name }}"
                                           placeholder="Field name"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <input type="text" 
                                           name="existing_overrides[{{ $loop->index }}][override_value]" 
                                           value="{{ $override->override_value }}"
                                           placeholder="Override value"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <select name="existing_overrides[{{ $loop->index }}][field_type]" 
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="string" {{ $override->field_type === 'string' ? 'selected' : '' }}>String</option>
                                        <option value="text" {{ $override->field_type === 'text' ? 'selected' : '' }}>Text</option>
                                        <option value="integer" {{ $override->field_type === 'integer' ? 'selected' : '' }}>Integer</option>
                                        <option value="boolean" {{ $override->field_type === 'boolean' ? 'selected' : '' }}>Boolean</option>
                                        <option value="json" {{ $override->field_type === 'json' ? 'selected' : '' }}>JSON</option>
                                        <option value="datetime" {{ $override->field_type === 'datetime' ? 'selected' : '' }}>DateTime</option>
                                    </select>
                                </div>
                                <div class="text-center">
                                    <button type="button" 
                                            class="remove-override text-red-600 hover:text-red-800"
                                            title="Remove override">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            
                            @if($mapping->fieldOverrides->isEmpty())
                            <div class="text-sm text-gray-500 text-center py-4">
                                No field overrides configured. Click "Add Override" to create custom field mappings.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sync Status -->
                @if($mapping->last_sync_at)
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sync Status</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <strong>Last Sync:</strong> {{ $mapping->last_sync_at->format('M j, Y g:i A') }}
                            </div>
                            <div>
                                <strong>Sync Status:</strong> 
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                    @if($mapping->sync_status === 'success') bg-green-100 text-green-800
                                    @elseif($mapping->sync_status === 'error') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($mapping->sync_status ?? 'pending') }}
                                </span>
                            </div>
                            <div class="md:text-right">
                                <form method="POST" action="{{ route('wlcms.admin.legacy.mappings.sync', $mapping) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-500">
                                        Sync Now
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        @if($mapping->sync_error)
                        <div class="mt-4 p-3 bg-red-50 rounded-md">
                            <h4 class="text-sm font-medium text-red-800">Last Sync Error</h4>
                            <p class="mt-1 text-sm text-red-700">{{ $mapping->sync_error }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t">
                    <a href="{{ route('wlcms.admin.legacy.mappings.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    console.log('=== SCRIPT TAG LOADED ===');
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== DOM READY ===');
        console.log('Testing field override functionality...');
        
        // Test if elements exist
        const addBtn = document.getElementById('add-override');
        const container = document.getElementById('field-overrides');
        
        console.log('Add button:', addBtn);
        console.log('Container:', container);
        
        if (addBtn && container) {
            console.log('Elements found, attaching listeners...');
            
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Add button clicked!');
                alert('Add Override button is working!');
                
                // Actually add the row
                const row = document.createElement('div');
                row.className = 'grid grid-cols-4 gap-4 items-center override-row mb-3';
                row.innerHTML = `
                    <input type="text" name="new_overrides[field_name][]" placeholder="Field name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <input type="text" name="new_overrides[override_value][]" placeholder="Override value" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <select name="new_overrides[field_type][]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="string">String</option>
                        <option value="text">Text</option>
                    </select>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">Remove</button>
                `;
                container.appendChild(row);
            });
        } else {
            console.error('Required elements not found!');
            console.log('Available elements with IDs:', Array.from(document.querySelectorAll('[id]')).map(el => el.id));
        }
        
        // Initialize WLCMS components if available (for when Vite is compiled)
        if (window.initWlcms) {
            console.log('initWlcms found, calling...');
            window.initWlcms();
        } else {
            console.log('initWlcms not found - Vite compilation needed');
        }
    });
    </script>
    @endpush
</x-admin-layout>