<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentTemplateSettings extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_content_template_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_id',
        'settings',
        'zones_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'zones_data' => 'array',
    ];

    /**
     * Get the content item that owns the template settings.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class, 'content_id');
    }

    /**
     * Get a specific setting value.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Set a specific setting value.
     */
    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
    }

    /**
     * Get zone data for a specific zone.
     */
    public function getZoneData(string $zoneId, mixed $default = null): mixed
    {
        $zonesData = $this->zones_data ?? [];
        return $zonesData[$zoneId] ?? $default;
    }

    /**
     * Set zone data for a specific zone.
     */
    public function setZoneData(string $zoneId, mixed $data): void
    {
        $zonesData = $this->zones_data ?? [];
        $zonesData[$zoneId] = $data;
        $this->zones_data = $zonesData;
    }

    /**
     * Merge settings with another array.
     */
    public function mergeSettings(array $newSettings): void
    {
        $settings = $this->settings ?? [];
        $this->settings = array_merge($settings, $newSettings);
    }

    /**
     * Merge zones data with another array.
     */
    public function mergeZonesData(array $newZonesData): void
    {
        $zonesData = $this->zones_data ?? [];
        $this->zones_data = array_merge($zonesData, $newZonesData);
    }

    /**
     * Check if a setting exists.
     */
    public function hasSetting(string $key): bool
    {
        $settings = $this->settings ?? [];
        return isset($settings[$key]);
    }

    /**
     * Check if zone data exists.
     */
    public function hasZoneData(string $zoneId): bool
    {
        $zonesData = $this->zones_data ?? [];
        return isset($zonesData[$zoneId]);
    }

    /**
     * Get all settings as array.
     */
    public function getAllSettings(): array
    {
        return $this->settings ?? [];
    }

    /**
     * Get all zones data as array.
     */
    public function getAllZonesData(): array
    {
        return $this->zones_data ?? [];
    }
}
