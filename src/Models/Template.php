<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identifier',
        'name',
        'description',
        'preview_image',
        'zones',
        'features',
        'settings_schema',
        'view_path',
        'category',
        'version',
        'is_default',
        'active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'zones' => 'array',
        'features' => 'array',
        'settings_schema' => 'array',
        'is_default' => 'boolean',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all content items using this template.
     */
    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class, 'template', 'identifier');
    }

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get templates by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get default templates.
     */
    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order templates by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if template has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return isset($features[$feature]) && $features[$feature] === true;
    }

    /**
     * Get zone configuration by zone identifier.
     */
    public function getZone(string $zoneId): ?array
    {
        $zones = $this->zones ?? [];
        return $zones[$zoneId] ?? null;
    }

    /**
     * Get all required zones.
     */
    public function getRequiredZones(): array
    {
        $zones = $this->zones ?? [];
        return array_filter($zones, function ($zone) {
            return ($zone['required'] ?? false) === true;
        });
    }

    /**
     * Get setting schema field by key.
     */
    public function getSettingSchema(string $key): ?array
    {
        $schema = $this->settings_schema ?? [];
        return $schema[$key] ?? null;
    }

    /**
     * Get default value for a setting.
     */
    public function getSettingDefault(string $key): mixed
    {
        $schema = $this->getSettingSchema($key);
        return $schema['default'] ?? null;
    }

    /**
     * Validate zones data against template zones configuration.
     */
    public function validateZonesData(array $zonesData): array
    {
        $errors = [];
        $requiredZones = $this->getRequiredZones();

        foreach ($requiredZones as $zoneId => $zoneConfig) {
            if (!isset($zonesData[$zoneId]) || empty($zonesData[$zoneId])) {
                $errors[$zoneId] = "The {$zoneConfig['label']} zone is required.";
            }
        }

        return $errors;
    }

    /**
     * Get template categories.
     */
    public static function getCategories(): array
    {
        return [
            'landing' => 'Landing Pages',
            'content' => 'Content Pages',
            'form' => 'Form Pages',
            'archive' => 'Archive Pages',
            'other' => 'Other',
        ];
    }
}
