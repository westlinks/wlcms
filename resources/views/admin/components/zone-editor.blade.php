{{-- resources/views/admin/components/zone-editor.blade.php --}}
@props(['zones', 'zoneData'])

<div x-data="{
    zoneData: @js($zoneData),
    updateZone(key, value) { this.zoneData[key] = value; }
}" class="wlcms-zone-editor space-y-8">
    @foreach ($zones as $zone)
        <div class="zone-block bg-white rounded shadow p-6">
            <h3 class="text-lg font-semibold mb-2">
                {{ $zone['label'] ?? ucfirst($zone['key']) }}
                @if (!empty($zone['required'])) <span class="text-red-500">*</span>@endif
            </h3>
            @includeIf('wlcms::admin.components.zones.' . $zone['type'], [
                'zone' => $zone,
                'value' => $zoneData[$zone['key']] ?? null,
                'onUpdate' => 'updateZone',
                'zoneKey' => $zone['key'],
            ])
        </div>
    @endforeach
    <input type="hidden" name="zones" :value="JSON.stringify(zoneData)">
</div>
