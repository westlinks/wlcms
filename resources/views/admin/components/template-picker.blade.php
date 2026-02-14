@props(['name' => 'template_identifier', 'selected' => null, 'label' => 'Choose Template'])

@php
    // Get all available templates from the database
    $templates = \Westlinks\Wlcms\Models\Template::orderBy('name')->get();
    $selectedTemplate = $selected ? \Westlinks\Wlcms\Models\Template::where('identifier', $selected)->first() : null;
    
    // Prepare data for Alpine - encode for safe use in HTML attribute
    $alpineData = json_encode([
        'selectedTemplate' => $selectedTemplate,
        'allTemplates' => $templates->toArray(),
        'showModal' => !$selectedTemplate,
        'searchQuery' => '',
        'filterFeature' => ''
    ]);
@endphp

<div x-data="{
    selectedTemplate: @js($selectedTemplate),
    allTemplates: @js($templates->toArray()),
    showModal: {{ $selectedTemplate ? 'false' : 'true' }},
    searchQuery: '',
    filterFeature: '',
    get filteredTemplates() {
        let filtered = this.allTemplates;
        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(t => 
                t.name.toLowerCase().includes(query) || 
                (t.description && t.description.toLowerCase().includes(query))
            );
        }
        if (this.filterFeature) {
            filtered = filtered.filter(t => 
                t.features && t.features.includes(this.filterFeature)
            );
        }
        return filtered;
    },
    selectTemplate(template) {
        this.selectedTemplate = template;
        // Dispatch event for zone editor to react
        window.dispatchEvent(new CustomEvent('template-selected', {
            detail: { template: template }
        }));
    }
}" 
x-init="if (selectedTemplate) { 
    setTimeout(() => {
        window.dispatchEvent(new CustomEvent('template-selected', { 
            detail: { template: selectedTemplate } 
        }));
    }, 100);
}" 
class="template-picker">
    {{-- Hidden input to store selected template identifier --}}
    <input type="hidden" name="{{ $name }}" :value="selectedTemplate?.identifier" />

    {{-- Header --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ $label }}</h3>
        @if($selectedTemplate)
            <button type="button" @click="showModal = true" class="text-sm text-blue-600 hover:text-blue-700">
                Change Template
            </button>
        @endif
    </div>

    {{-- Current Selection Display --}}
    <div x-show="selectedTemplate" class="bg-gray-50 border rounded-lg p-4 mb-4">
        <div class="flex items-start space-x-4">
            {{-- Template Preview Image --}}
            <div class="flex-shrink-0 w-32 h-24 bg-white border rounded overflow-hidden">
                <img :src="selectedTemplate?.preview_image || '/vendor/wlcms/images/template-placeholder.svg'" 
                     :alt="selectedTemplate?.name"
                     class="w-full h-full object-cover">
            </div>

            {{-- Template Info --}}
            <div class="flex-1">
                <h4 class="font-medium text-gray-900" x-text="selectedTemplate?.name"></h4>
                <p class="text-sm text-gray-600 mt-1" x-text="selectedTemplate?.description"></p>
                
                {{-- Features --}}
                <div class="flex flex-wrap gap-2 mt-2" x-show="selectedTemplate?.features && Object.keys(selectedTemplate.features).length > 0">
                    <template x-for="[featureKey, featureValue] in Object.entries(selectedTemplate?.features || {})" :key="featureKey">
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded" x-show="featureValue">
                            <span x-text="featureKey.replace(/_/g, ' ')"></span>
                        </span>
                    </template>
                </div>
            </div>

            {{-- Change Button --}}
            <button type="button" @click="showModal = true" 
                    class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50">
                Change
            </button>
        </div>
    </div>

    {{-- No Selection State --}}
    <div x-show="!selectedTemplate" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M4 5a1 1 0 011-1h4a1 1 0 010 2H6v13h12V6h-3a1 1 0 110-2h4a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M8 3h8v2H8V3zm4 4v8m-4-4h8"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No template selected</h3>
        <p class="mt-1 text-sm text-gray-500">Choose a template to define the layout and structure</p>
        <button type="button" @click="showModal = true" 
                class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Select Template
        </button>
    </div>

    {{-- Template Selection Modal --}}
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         @keydown.escape.window="showModal = false">
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
             @click="showModal = false"></div>

        {{-- Modal Panel --}}
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden"
                 @click.stop>
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Choose a Template</h2>
                        <p class="text-sm text-gray-600 mt-1">Select a template to define your content layout and features</p>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Search/Filter Bar --}}
                <div class="p-4 border-b bg-gray-50">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" 
                                   x-model="searchQuery"
                                   placeholder="Search templates..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <select x-model="filterFeature" 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Features</option>
                            <option value="responsive">Responsive</option>
                            <option value="seo">SEO Optimized</option>
                            <option value="form">Form Support</option>
                            <option value="gallery">Gallery</option>
                            <option value="seasonal">Seasonal Content</option>
                        </select>
                    </div>
                </div>

                {{-- Template Grid --}}
                <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="template in filteredTemplates" :key="template.identifier">
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
                                 :class="{ 'ring-2 ring-blue-500': selectedTemplate?.identifier === template.identifier }"
                                 @click="selectTemplate(template)">
                                
                                {{-- Preview Image --}}
                                <div class="aspect-video bg-gray-100 border-b">
                                    <img :src="template.preview_image || '/vendor/wlcms/images/template-placeholder.svg'" 
                                         :alt="template.name"
                                         class="w-full h-full object-cover">
                                </div>

                                {{-- Template Info --}}
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="font-medium text-gray-900" x-text="template.name"></h3>
                                        <span x-show="selectedTemplate?.identifier === template.identifier"
                                              class="flex-shrink-0 ml-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-3" x-text="template.description"></p>

                                    {{-- Features --}}
                                    <div class="flex flex-wrap gap-1.5" x-show="template.features && Object.keys(template.features).length > 0">
                                        <template x-for="[featureKey, featureValue] in Object.entries(template.features || {})" :key="featureKey">
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded" x-show="featureValue">
                                                <span x-text="featureKey.replace(/_/g, ' ')"></span>
                                            </span>
                                        </template>
                                    </div>

                                    {{-- Zones Info --}}
                                    <div class="mt-3 pt-3 border-t text-xs text-gray-500">
                                        <span x-text="Object.keys(template.zones || {}).length + ' content zones'"></span>
                                    </div>
                                </div>

                                {{-- Select Button --}}
                                <div class="px-4 pb-4">
                                    <button type="button" 
                                            @click.stop="selectTemplate(template); showModal = false"
                                            class="w-full px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                                            :class="selectedTemplate?.identifier === template.identifier ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 hover:bg-gray-700'">
                                        <span x-text="selectedTemplate?.identifier === template.identifier ? 'Selected' : 'Select Template'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- No Results --}}
                    <div x-show="filteredTemplates.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No templates found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter</p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                    <button type="button" @click="showModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>


