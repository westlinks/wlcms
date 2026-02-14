{{-- resources/views/admin/components/zones/repeater.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
<div x-data="{
    items: @js($value ?? []),
    addItem() { this.items.push({ title: '', icon: '', text: '' }); },
    removeItem(idx) { this.items.splice(idx, 1); },
    update() { $dispatch('{{ $onUpdate ?? 'updateZone' }}', { key: '{{ $zoneKey }}', value: this.items }); }
}" class="space-y-4">
    <template x-for="(item, idx) in items" :key="idx">
        <div class="border-b pb-4 mb-4">
            <input x-model="item.title" @input="update()" class="input mb-2" placeholder="Title">
            <input x-model="item.icon" @input="update()" class="input mb-2" placeholder="Icon">
            <textarea x-model="item.text" @input="update()" class="input mb-2" placeholder="Text"></textarea>
            <button type="button" @click="removeItem(idx); update()" class="btn btn-danger">Remove</button>
        </div>
    </template>
    <button type="button" @click="addItem(); update()" class="btn btn-primary">+ Add Item</button>
</div>
