<x-admin-layout title="Navigation Management">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Legacy Navigation Management') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('wlcms.admin.legacy.index') }}" class="hover:text-gray-700">Legacy Integration</a>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
            </svg>
            <span>Navigation Management</span>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-3">
                <a href="{{ route('wlcms.admin.legacy.navigation.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Navigation Item
                </a>
                
                <button type="button" 
                        onclick="bulkSyncNavigation()" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Sync All Navigation
                </button>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Show:</span>
                <select id="filterStatus" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">All Items</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="error">Error</option>
                </select>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Navigation Tree -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Navigation Hierarchy
                </h3>
                
                <div class="space-y-2" id="navigationTree">
                    @forelse($navigationItems->where('parent_id', null) as $rootItem)
                        @include('wlcms::admin.legacy.navigation.partials.tree-item', ['item' => $rootItem, 'level' => 0])
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v3m10 4a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No navigation items</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first navigation item.</p>
                            <div class="mt-6">
                                <a href="{{ route('wlcms.admin.legacy.navigation.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Add Navigation Item
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Bulk Operations
                </h3>
                
                <form id="bulkActionForm" method="POST" action="{{ route('wlcms.admin.legacy.navigation.bulk') }}">
                    @csrf
                    <input type="hidden" name="action" id="bulkAction">
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <button type="button" onclick="setBulkAction('activate')" 
                                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Activate Selected
                        </button>
                        
                        <button type="button" onclick="setBulkAction('deactivate')" 
                                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            Deactivate Selected
                        </button>
                        
                        <button type="button" onclick="setBulkAction('sync')" 
                                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
                            </svg>
                            Sync Selected
                        </button>
                        
                        <button type="button" onclick="setBulkAction('delete')" 
                                class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-red-700 hover:bg-red-50">
                            <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h4a2 2 0 002-2V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z"/>
                            </svg>
                            Delete Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Import/Export -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Import Navigation -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Import Navigation
                    </h3>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">
                            Import navigation structure from legacy system or CSV file.
                        </p>
                        
                        <form method="POST" action="{{ route('wlcms.admin.legacy.navigation.import') }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label for="import_type" class="block text-sm font-medium text-gray-700">Import Type</label>
                                <select name="import_type" id="import_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="legacy_auto">Auto-detect from Legacy System</option>
                                    <option value="csv_file">Upload CSV File</option>
                                    <option value="json_file">Upload JSON File</option>
                                </select>
                            </div>
                            
                            <div id="fileUploadSection" style="display: none;">
                                <label for="navigation_file" class="block text-sm font-medium text-gray-700">Navigation File</label>
                                <input type="file" name="navigation_file" id="navigation_file" accept=".csv,.json" 
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="preserve_existing" value="1" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Preserve existing items</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="create_redirects" value="1" checked 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Create redirects</span>
                                </label>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                Import Navigation
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Export Navigation -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Export Navigation
                    </h3>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">
                            Export current navigation structure for backup or migration.
                        </p>
                        
                        <form method="GET" action="{{ route('wlcms.admin.legacy.navigation.export') }}" class="space-y-4">
                            
                            <div>
                                <label for="export_format" class="block text-sm font-medium text-gray-700">Export Format</label>
                                <select name="format" id="export_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="csv">CSV (Spreadsheet)</option>
                                    <option value="json">JSON (Structured)</option>
                                    <option value="xml">XML (Legacy Compatible)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="export_scope" class="block text-sm font-medium text-gray-700">Export Scope</label>
                                <select name="scope" id="export_scope" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="all">All Navigation Items</option>
                                    <option value="active">Active Items Only</option>
                                    <option value="legacy_mapped">Legacy Mapped Only</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_hierarchy" value="1" checked 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Include hierarchy</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_metadata" value="1" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Include metadata</span>
                                </label>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                Export Navigation
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Filter functionality
        document.getElementById('filterStatus').addEventListener('change', function() {
            const status = this.value;
            const items = document.querySelectorAll('[data-status]');
            
            items.forEach(item => {
                if (status === '' || item.dataset.status === status) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Import type change
        document.getElementById('import_type').addEventListener('change', function() {
            const fileSection = document.getElementById('fileUploadSection');
            if (this.value !== 'legacy_auto') {
                fileSection.style.display = 'block';
            } else {
                fileSection.style.display = 'none';
            }
        });

        // Bulk actions
        function setBulkAction(action) {
            const checkedItems = document.querySelectorAll('input[name="selected[]"]:checked');
            if (checkedItems.length === 0) {
                alert('Please select at least one navigation item.');
                return;
            }
            
            let message;
            switch(action) {
                case 'activate':
                    message = `Activate ${checkedItems.length} selected items?`;
                    break;
                case 'deactivate':
                    message = `Deactivate ${checkedItems.length} selected items?`;
                    break;
                case 'sync':
                    message = `Sync ${checkedItems.length} selected items?`;
                    break;
                case 'delete':
                    message = `Permanently delete ${checkedItems.length} selected items? This action cannot be undone.`;
                    break;
            }
            
            if (confirm(message)) {
                document.getElementById('bulkAction').value = action;
                document.getElementById('bulkActionForm').submit();
            }
        }

        function bulkSyncNavigation() {
            if (confirm('This will sync all navigation items. Are you sure?')) {
                // Submit form or make AJAX request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("wlcms.admin.legacy.navigation.sync-all") }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Toggle children visibility
        function toggleChildren(itemId) {
            const children = document.getElementById('children-' + itemId);
            const toggle = document.getElementById('toggle-' + itemId);
            
            if (children.style.display === 'none') {
                children.style.display = 'block';
                toggle.innerHTML = '▼';
            } else {
                children.style.display = 'none';
                toggle.innerHTML = '▶';
            }
        }

        // Select all functionality
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('input[name="selected[]"]');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }
    </script>
</x-admin-layout>