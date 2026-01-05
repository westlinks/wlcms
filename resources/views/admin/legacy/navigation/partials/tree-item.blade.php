<div class="flex items-center space-x-3 py-2 pl-{{ $level * 6 + 4 }} border-l-2 border-gray-200" 
     data-status="{{ $item->is_active ? 'active' : 'inactive' }}">
    
    <!-- Expand/Collapse Toggle -->
    @if($item->children->count() > 0)
        <button type="button" 
                id="toggle-{{ $item->id }}" 
                onclick="toggleChildren({{ $item->id }})" 
                class="text-gray-400 hover:text-gray-600 focus:outline-none">
            ▼
        </button>
    @else
        <span class="w-4"></span>
    @endif
    
    <!-- Checkbox for bulk actions -->
    <input type="checkbox" name="selected[]" value="{{ $item->id }}" 
           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
    
    <!-- Status Indicator -->
    <div class="flex-shrink-0">
        @if($item->is_active)
            <div class="h-2 w-2 bg-green-400 rounded-full"></div>
        @else
            <div class="h-2 w-2 bg-gray-400 rounded-full"></div>
        @endif
    </div>
    
    <!-- Navigation Item Details -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $item->title }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ $item->legacy_url }}
                        @if($item->cms_url)
                            → {{ $item->cms_url }}
                        @endif
                    </p>
                </div>
                
                @if($item->legacy_item_id)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        Legacy ID: {{ $item->legacy_item_id }}
                    </span>
                @endif
                
                @if($item->last_sync_at)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                        Synced: {{ $item->last_sync_at->diffForHumans() }}
                    </span>
                @endif
                
                @if($item->sync_error)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                        Error
                    </span>
                @endif
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('wlcms.admin.legacy.navigation.edit', $item) }}" 
                   class="text-blue-600 hover:text-blue-900 text-sm">
                    Edit
                </a>
                
                <form method="POST" action="{{ route('wlcms.admin.legacy.navigation.sync', $item) }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="text-green-600 hover:text-green-900 text-sm"
                            onclick="return confirm('Sync this navigation item?')">
                        Sync
                    </button>
                </form>
                
                @if($item->is_active)
                    <form method="POST" action="{{ route('wlcms.admin.legacy.navigation.deactivate', $item) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="text-yellow-600 hover:text-yellow-900 text-sm"
                                onclick="return confirm('Deactivate this navigation item?')">
                            Deactivate
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('wlcms.admin.legacy.navigation.activate', $item) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="text-green-600 hover:text-green-900 text-sm"
                                onclick="return confirm('Activate this navigation item?')">
                            Activate
                        </button>
                    </form>
                @endif
                
                <form method="POST" action="{{ route('wlcms.admin.legacy.navigation.destroy', $item) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="text-red-600 hover:text-red-900 text-sm"
                            onclick="return confirm('Delete this navigation item? This action cannot be undone.')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Additional Information -->
        @if($item->description)
            <p class="mt-1 text-xs text-gray-600">{{ $item->description }}</p>
        @endif
        
        <!-- Metadata -->
        <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
            <span>Order: {{ $item->sort_order }}</span>
            @if($item->target_type)
                <span>Target: {{ ucfirst($item->target_type) }}</span>
            @endif
            @if($item->css_classes)
                <span>Classes: {{ $item->css_classes }}</span>
            @endif
        </div>
    </div>
</div>

<!-- Children -->
@if($item->children->count() > 0)
    <div id="children-{{ $item->id }}" class="border-l border-gray-200 ml-{{ $level * 6 + 8 }}">
        @foreach($item->children->sortBy('sort_order') as $child)
            @include('wlcms::admin.legacy.navigation.partials.tree-item', ['item' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif