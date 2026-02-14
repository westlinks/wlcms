{{-- resources/views/admin/components/zones/form_embed.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
<div x-data="{
    embedCode: @js($value['embed_code'] ?? ''),
    update() { $dispatch('{{ $onUpdate ?? 'updateZone' }}', { key: '{{ $zoneKey }}', value: { embed_code: this.embedCode } }); }
}" class="space-y-2">
    <label class="block font-semibold mb-1">Form Embed Code</label>
    <textarea x-model="embedCode" @input="update()" class="input w-full" rows="4" placeholder="Paste form embed code here..."></textarea>
</div>
