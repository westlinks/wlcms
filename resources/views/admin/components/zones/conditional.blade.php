{{-- resources/views/admin/components/zones/conditional.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
<div x-data="{
    enabled: @js($value['enabled'] ?? false),
    content: @js($value['content'] ?? ''),
    update() { $dispatch('{{ $onUpdate ?? 'updateZone' }}', { key: '{{ $zoneKey }}', value: { enabled: this.enabled, content: this.content } }); }
}" class="space-y-2">
    <label class="flex items-center gap-2">
        <input type="checkbox" x-model="enabled" @change="update()">
        Enable Conditional Content
    </label>
    <div x-show="enabled" class="mt-2">
        <textarea x-model="content" @input="update()" class="input w-full" rows="3" placeholder="Conditional content..."></textarea>
    </div>
</div>
