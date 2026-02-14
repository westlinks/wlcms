{{-- resources/views/admin/components/zones/link_list.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
<div x-data="{
    links: @js($value ?? []),
    addLink() { this.links.push({ label: '', url: '' }); },
    remove(idx) { this.links.splice(idx, 1); },
    update() { $dispatch('{{ $onUpdate ?? 'updateZone' }}', { key: '{{ $zoneKey }}', value: this.links }); }
}" class="space-y-2">
    <div class="space-y-2">
        <template x-for="(link, idx) in links" :key="idx">
            <div class="flex gap-2 items-center">
                <input x-model="link.label" @input="update()" class="input" placeholder="Label">
                <input x-model="link.url" @input="update()" class="input" placeholder="URL">
                <button type="button" @click="remove(idx); update()" class="btn btn-danger">Remove</button>
            </div>
        </template>
    </div>
    <button type="button" @click="addLink(); update()" class="btn btn-primary">+ Add Link</button>
</div>
