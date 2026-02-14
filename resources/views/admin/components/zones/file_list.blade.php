{{-- resources/views/admin/components/zones/file_list.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
<div x-data="{
    files: @js($value ?? []),
    addFiles(e) {
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = evt => {
                this.files.push({ url: evt.target.result, name: file.name, description: '' });
            };
            reader.readAsDataURL(file);
        });
    },
    remove(idx) { this.files.splice(idx, 1); },
    update() { $dispatch('{{ $onUpdate ?? 'updateZone' }}', { key: '{{ $zoneKey }}', value: this.files }); }
}" class="space-y-2">
    <div class="space-y-2">
        <template x-for="(file, idx) in files" :key="idx">
            <div class="flex items-center gap-2 border-b pb-2">
                <a :href="file.url" target="_blank" class="underline">{{ file.name }}</a>
                <input x-model="file.description" @input="update()" class="input" placeholder="Description">
                <button type="button" @click="remove(idx); update()" class="btn btn-danger">Remove</button>
            </div>
        </template>
    </div>
    <input type="file" multiple accept="application/pdf" @change="addFiles($event); update()" class="input">
</div>
