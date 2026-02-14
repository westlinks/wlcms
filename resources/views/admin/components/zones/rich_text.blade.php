{{-- resources/views/admin/components/zones/rich_text.blade.php --}}
@props(['zone', 'value', 'zoneKey', 'onUpdate'])
<div>
    @include('wlcms::admin.components.editor', [
        'name' => 'zone_' . $zoneKey,
        'value' => $value,
        'label' => $zone['label'] ?? ucfirst($zoneKey),
        'required' => $zone['required'] ?? false
    ])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.querySelector('[name="zone_{{ $zoneKey }}"]');
            if (textarea) {
                textarea.addEventListener('change', function() {
                    $dispatch('{{ $onUpdate ?? 'updateZone' }}', { key: '{{ $zoneKey }}', value: textarea.value });
                });
            }
        });
    </script>
</div>
