{{-- WLCMS Media Picker Component --}}
<div 
    x-data="{
        selectedMedia: {{ $multiple ? '[]' : 'null' }},
        currentValue: '{{ $value ?? '' }}',
        
        init() {
            // Initialize from existing value if provided
            if (this.currentValue) {
                @if($multiple)
                    // For multiple selection, parse comma-separated paths
                    const paths = this.currentValue.split(',').filter(p => p.trim());
                    this.selectedMedia = paths.map(path => ({ path: path.trim() }));
                @else
                    // For single selection, store as object with path
                    this.selectedMedia = { path: this.currentValue };
                @endif
            }
        },
        
        openPicker() {
            if (window.mediaPicker) {
                window.mediaPicker.open(
                    (selected) => {
                        @if($multiple)
                            // Multiple selection: array of media objects with full data
                            this.selectedMedia = selected;
                            // Store paths for backward compatibility
                            const paths = selected.map(m => m.path).join(',');
                            document.getElementById('{{ $uniqueId }}-input').value = paths;
                        @else
                            // Single selection: single media object with full data
                            this.selectedMedia = selected;
                            // Store path for backward compatibility
                            document.getElementById('{{ $uniqueId }}-input').value = selected.path;
                        @endif
                        
                        console.log('Media selected:', selected);
                    },
                    {
                        multiSelect: {{ $multiple ? 'true' : 'false' }},
                        mediaType: {{ $type ? "'" . $type . "'" : 'null' }}
                    }
                );
            } else {
                console.error('MediaPicker not initialized');
            }
        },
        
        clearSelection() {
            @if($multiple)
                this.selectedMedia = [];
            @else
                this.selectedMedia = null;
            @endif
            document.getElementById('{{ $uniqueId }}-input').value = '';
        },
        
        hasSelection() {
            @if($multiple)
                return this.selectedMedia.length > 0;
            @else
                return this.selectedMedia !== null;
            @endif
        },
        
        getPreviewUrl(media) {
            // If we have the media ID, use the proper serve route
            if (media.id) {
                return `/admin/cms/media/${media.id}/serve/small`;
            }
            // If media has thumbnail_url from picker, use it
            if (media.thumbnail_url) {
                return media.thumbnail_url;
            }
            // If media has url from picker, use it
            if (media.url) {
                return media.url;
            }
            // Fallback for existing data: just the path (may not display)
            // This shouldn't happen with new selections from the picker
            console.warn('Media preview: no ID or URL available', media);
            return media.path || '';
        }
    }"
    class="media-picker-component"
    id="{{ $uniqueId }}"
>
    {{-- Label --}}
    @if($label)
        <label for="{{ $uniqueId }}-input" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    {{-- Hidden Input (actual form field) --}}
    <input 
        type="hidden" 
        name="{{ $field }}" 
        id="{{ $uniqueId }}-input"
        value="{{ $value ?? '' }}"
        @if($required) required @endif
    >
    
    {{-- Preview & Controls --}}
    <div class="space-y-2">
        {{-- Preview Area --}}
        <div x-show="hasSelection()" class="flex flex-wrap gap-2">
            @if($multiple)
                {{-- Multiple selection: show multiple thumbnails --}}
                <template x-for="(media, index) in selectedMedia" :key="index">
                    <div class="relative w-20 h-20 border border-gray-300 rounded overflow-hidden bg-gray-100">
                        <img 
                            :src="getPreviewUrl(media)" 
                            :alt="media.name || 'Selected media'"
                            class="w-full h-full object-cover"
                        >
                        <button 
                            type="button"
                            @click.prevent="selectedMedia.splice(index, 1); document.getElementById('{{ $uniqueId }}-input').value = selectedMedia.map(m => m.path).join(',');"
                            class="absolute top-0 right-0 bg-red-500 text-white rounded-bl px-1 text-xs hover:bg-red-600"
                            title="Remove"
                        >
                            ×
                        </button>
                    </div>
                </template>
            @else
                {{-- Single selection: show single thumbnail --}}
                <div class="w-24 h-24 border border-gray-300 rounded overflow-hidden bg-gray-100">
                    <img 
                        x-show="selectedMedia"
                        :src="selectedMedia ? getPreviewUrl(selectedMedia) : ''" 
                        :alt="selectedMedia ? (selectedMedia.name || 'Selected media') : ''"
                        class="w-full h-full object-cover"
                    >
                </div>
            @endif
        </div>
        
        {{-- Action Buttons --}}
        <div class="flex gap-2">
            <button 
                type="button" 
                @click="openPicker()" 
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Browse Media Library
            </button>
            
            <button 
                type="button" 
                @click="clearSelection()" 
                x-show="hasSelection()"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
            >
                Clear
            </button>
        </div>
    </div>
    
    {{-- Help Text --}}
    @if($helpText)
        <p class="mt-2 text-sm text-gray-500">{{ $helpText }}</p>
    @endif
    
    {{-- Error Display (Laravel Validation) --}}
    @error($field)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Auto-inject modal once per page using stack --}}
@once
    @push('modals')
        @include('wlcms::admin.components.media-picker')
    @endpush
@endonce
