{{-- resources/views/admin/components/zones/media_gallery.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
<div x-data="{
    items: @js($value ?? []),
    addFiles(e) {
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = evt => {
                this.items.push({ url: evt.target.result, alt: '', caption: '' });
            };
            reader.readAsDataURL(file);
        });
    },
    remove(idx) { this.items.splice(idx, 1); },
    update() { $dispatch('{{ $onUpdate ?? 'updateZone' }}', { key: '{{ $zoneKey }}', value: this.items }); }
}" class="space-y-2">
    <div class="flex flex-wrap gap-4 mb-2">
        <template x-for="(item, idx) in items" :key="idx">
            <div class="border rounded p-2 flex flex-col items-center">
                <img :src="item.url" class="w-20 h-20 object-cover mb-1">
                <input x-model="item.alt" @input="update()" class="input mb-1" placeholder="Alt text">
                <input x-model="item.caption" @input="update()" class="input mb-1" placeholder="Caption">
                <button type="button" @click="remove(idx); update()" class="btn btn-danger">Remove</button>
            </div>
        </template>
    </div>
    <input type="file" multiple @change="addFiles($event); update()" class="input">
</div>
