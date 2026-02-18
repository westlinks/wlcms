{{-- resources/views/admin/components/zones/form_embed.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
@php
    $formRegistry = app(\Westlinks\Wlcms\Services\FormRegistry::class);
    $availableForms = $formRegistry->all();
@endphp
<div x-data="{
    formId: @js($value['form_id'] ?? ''),
    embedCode: @js($value['embed_code'] ?? ''),
    embedType: @js($value['embed_type'] ?? 'built-in'),
    update() { 
        window.dispatchEvent(new CustomEvent('updatezone', { 
            detail: {
                key: '{{ $zoneKey }}', 
                value: { 
                    form_id: this.formId,
                    embed_code: this.embedCode,
                    embed_type: this.embedType
                }
            },
            bubbles: true
        })); 
    },
    insertShortcode() {
        if (this.formId) {
            this.embedCode = '[form id=&quot;' + this.formId + '&quot;]';
            this.update();
        }
    }
}" class="space-y-4 border rounded p-4 bg-gray-50">
    
    {{-- Embed Type Selector --}}
    <div>
        <label class="block text-sm font-semibold mb-2">Form Type</label>
        <div class="flex space-x-4">
            <label class="inline-flex items-center">
                <input type="radio" x-model="embedType" value="built-in" @change="update()" class="mr-2">
                <span class="text-sm">Built-in Form</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" x-model="embedType" value="shortcode" @change="update()" class="mr-2">
                <span class="text-sm">Shortcode</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" x-model="embedType" value="external" @change="update()" class="mr-2">
                <span class="text-sm">External Embed Code</span>
            </label>
        </div>
    </div>

    {{-- Built-in Form Selector --}}
    <div x-show="embedType === 'built-in'" x-cloak>
        <label class="block text-sm font-semibold mb-2">Select Form</label>
        <select x-model="formId" @change="insertShortcode()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Select a Form --</option>
            @foreach ($availableForms as $form)
                <option value="{{ $form['identifier'] }}">{{ $form['name'] }}</option>
            @endforeach
        </select>
        @if ($availableForms->isEmpty())
            <p class="text-sm text-gray-500 mt-2">No forms available. Forms can be registered in the service provider.</p>
        @endif
    </div>

    {{-- Shortcode Manual Entry --}}
    <div x-show="embedType === 'shortcode'" x-cloak>
        <label class="block text-sm font-semibold mb-2">Form Shortcode</label>
        <input 
            type="text" 
            x-model="embedCode" 
            @input="update()" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
            placeholder='[form id="contact"]'
        >
        <p class="text-xs text-gray-500 mt-1">Example: [form id="contact"] or [form id="newsletter"]</p>
    </div>

    {{-- External Embed Code --}}
    <div x-show="embedType === 'external'" x-cloak>
        <label class="block text-sm font-semibold mb-2">External Form Embed Code</label>
        <textarea 
            x-model="embedCode" 
            @input="update()" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
            rows="6" 
            placeholder="Paste external form embed code (HTML or iframe) here..."
        ></textarea>
        <p class="text-xs text-gray-500 mt-1">Paste embed code from services like Google Forms, Typeform, JotForm, etc.</p>
    </div>

    {{-- Preview --}}
    <div x-show="embedCode" class="border-t pt-3">
        <label class="block text-sm font-semibold mb-2 text-gray-600">Preview Code:</label>
        <div class="bg-white border rounded p-3 text-xs font-mono text-gray-700 overflow-x-auto">
            <code x-text="embedCode"></code>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
