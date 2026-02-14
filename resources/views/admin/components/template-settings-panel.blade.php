{{--
Template Settings Panel Component
Dynamically generates form fields based on template's settings_schema
--}}

@props([
    'template' => null,
    'settings' => []
])

<div x-data="templateSettings" class="template-settings-panel">
    {{-- Settings will be shown when a template with settings is selected --}}
    <div x-show="hasSettings" x-cloak class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            Template Settings
        </h3>
        <p class="text-sm text-gray-600 mb-6">
            Configure template-specific options and appearance.
        </p>

        <div class="space-y-4">
            {{-- Dynamic Settings Fields --}}
            <template x-for="(field, key) in settingsSchema" :key="key">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    
                    {{-- Text Input --}}
                    <div x-show="field.type === 'text'">
                        <label :for="'setting_' + key" class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="field.label || key"></span>
                        </label>
                        <input 
                            type="text"
                            :id="'setting_' + key"
                            x-model="settingsData[key]"
                            :placeholder="field.placeholder || ''"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        />
                        <p x-show="field.description" x-text="field.description" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    {{-- Number Input --}}
                    <div x-show="field.type === 'number'">
                        <label :for="'setting_' + key" class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="field.label || key"></span>
                        </label>
                        <input 
                            type="number"
                            :id="'setting_' + key"
                            x-model="settingsData[key]"
                            :min="field.min || ''"
                            :max="field.max || ''"
                            :step="field.step || '1'"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        />
                        <p x-show="field.description" x-text="field.description" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    {{-- Select/Dropdown --}}
                    <div x-show="field.type === 'select'">
                        <label :for="'setting_' + key" class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="field.label || key"></span>
                        </label>
                        <select 
                            :id="'setting_' + key"
                            x-model="settingsData[key]"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">-- Select --</option>
                            <template x-for="(optionLabel, optionValue) in field.options" :key="optionValue">
                                <option :value="optionValue" x-text="optionLabel"></option>
                            </template>
                        </select>
                        <p x-show="field.description" x-text="field.description" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    {{-- Toggle/Checkbox --}}
                    <div x-show="field.type === 'toggle' || field.type === 'boolean'">
                        <label class="flex items-center cursor-pointer">
                            <input 
                                type="checkbox"
                                :id="'setting_' + key"
                                x-model="settingsData[key]"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm font-medium text-gray-700" x-text="field.label || key"></span>
                        </label>
                        <p x-show="field.description" x-text="field.description" class="text-xs text-gray-500 mt-1 ml-6"></p>
                    </div>

                    {{-- Media Picker --}}
                    <div x-show="field.type === 'media'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="field.label || key"></span>
                        </label>
                        
                        {{-- Media Preview --}}
                        <div x-show="settingsData[key] && typeof settingsData[key] === 'number'" class="mb-2">
                            <div class="relative inline-block">
                                <img 
                                    :src="settingsData[key] && typeof settingsData[key] === 'number' ? getMediaPreview(settingsData[key]) : ''"
                                    alt="Selected media"
                                    class="w-32 h-32 object-cover rounded border border-gray-300"
                                />
                                <button 
                                    type="button"
                                    @click="settingsData[key] = null"
                                    class="absolute top-1 right-1 bg-red-600 text-white px-2 py-1 text-xs rounded hover:bg-red-700">
                                    Remove
                                </button>
                            </div>
                        </div>
                        
                        {{-- Select Button --}}
                        <button 
                            type="button"
                            @click="openMediaPicker(key)"
                            class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">
                            <span x-show="!settingsData[key]">📷 Select Media</span>
                            <span x-show="settingsData[key]">Change Media</span>
                        </button>
                        <p x-show="field.description" x-text="field.description" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    {{-- Color Picker --}}
                    <div x-show="field.type === 'color'">
                        <label :for="'setting_' + key" class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="field.label || key"></span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="color"
                                :id="'setting_' + key"
                                x-model="settingsData[key]"
                                class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                            />
                            <input 
                                type="text"
                                x-model="settingsData[key]"
                                placeholder="#000000"
                                class="flex-1 border border-gray-300 rounded px-3 py-2"
                            />
                        </div>
                        <p x-show="field.description" x-text="field.description" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    {{-- Date Picker --}}
                    <div x-show="field.type === 'date'">
                        <label :for="'setting_' + key" class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="field.label || key"></span>
                        </label>
                        <input 
                            type="date"
                            :id="'setting_' + key"
                            x-model="settingsData[key]"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        />
                        <p x-show="field.description" x-text="field.description" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                </div>
            </template>
        </div>

        {{-- Hidden input to store all settings as JSON --}}
        <input type="hidden" name="settings_json" :value="JSON.stringify(settingsData)">
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('templateSettings', () => ({
        settingsSchema: {},
        settingsData: @js((object)($settings ?? [])),
        currentMediaSettingKey: null,

        get hasSettings() {
            return this.settingsSchema && Object.keys(this.settingsSchema).length > 0;
        },

        init() {
            console.log('Template settings panel initialized');
            console.log('Initial settingsData:', this.settingsData);
            
            // Listen for template selection
            window.addEventListener('template-selected', (event) => {
                console.log('Template selected event received:', event.detail.template);
                this.loadTemplateSettings(event.detail.template);
            });
        },

        loadTemplateSettings(template) {
            console.log('Loading template settings for:', template?.name);
            console.log('Current settingsData:', this.settingsData);
            
            if (!template || !template.settings_schema) {
                this.settingsSchema = {};
                return;
            }

            this.settingsSchema = template.settings_schema;
            console.log('Settings schema:', this.settingsSchema);

            // Initialize settings data with defaults only if no value exists
            Object.keys(this.settingsSchema).forEach(key => {
                const field = this.settingsSchema[key];
                
                // Only set defaults if there's no existing saved value
                if (this.settingsData[key] === undefined || this.settingsData[key] === null || this.settingsData[key] === '') {
                    if (field.default !== undefined) {
                        this.settingsData[key] = field.default;
                    } else if (field.type === 'toggle' || field.type === 'boolean') {
                        this.settingsData[key] = false;
                    }
                }
            });
            
            console.log('Settings data after initialization:', this.settingsData);
        },

        openMediaPicker(settingKey) {
            this.currentMediaSettingKey = settingKey;
            
            if (window.mediaPicker) {
                window.mediaPicker.open((media) => {
                    this.settingsData[settingKey] = media.id;
                });
            } else {
                alert('Media picker not available. Please refresh the page.');
            }
        },

        getMediaPreview(mediaId) {
            if (!mediaId) return '';
            // Use the actual media serve route
            return `{{ config('wlcms.admin.prefix', 'admin/cms') }}/media/${mediaId}/serve/thumbnail`;
        }
    }));
});
</script>
