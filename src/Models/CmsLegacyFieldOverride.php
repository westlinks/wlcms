<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CmsLegacyFieldOverride extends Model
{
    use HasFactory;

    protected $table = 'cms_legacy_field_overrides';

    protected $fillable = [
        'cms_legacy_article_mapping_id',
        'field_name',
        'override_value',
        'field_type',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the article mapping that owns this field override
     */
    public function articleMapping(): BelongsTo
    {
        return $this->belongsTo(CmsLegacyArticleMapping::class, 'cms_legacy_article_mapping_id');
    }

    /**
     * Get the typed value of the override
     */
    public function getTypedValue()
    {
        return match ($this->field_type) {
            'integer' => (int) $this->override_value,
            'boolean' => (bool) $this->override_value,
            'json' => json_decode($this->override_value, true),
            'datetime' => $this->override_value ? new \Carbon\Carbon($this->override_value) : null,
            'text', 'string' => $this->override_value,
            default => $this->override_value,
        };
    }

    /**
     * Set the typed value and field type
     */
    public function setTypedValue($value): void
    {
        if (is_int($value)) {
            $this->field_type = 'integer';
            $this->override_value = (string) $value;
        } elseif (is_bool($value)) {
            $this->field_type = 'boolean';
            $this->override_value = $value ? '1' : '0';
        } elseif (is_array($value)) {
            $this->field_type = 'json';
            $this->override_value = json_encode($value);
        } elseif ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            $this->field_type = 'datetime';
            $this->override_value = $value->toDateTimeString();
        } elseif (strlen($value) > 255) {
            $this->field_type = 'text';
            $this->override_value = $value;
        } else {
            $this->field_type = 'string';
            $this->override_value = $value;
        }
    }

    /**
     * Check if this field is a valid legacy field
     */
    public function isValidLegacyField(): bool
    {
        $fieldMappings = config('wlcms.legacy.field_mappings', []);
        return array_key_exists($this->field_name, $fieldMappings) || 
               in_array($this->field_name, $fieldMappings);
    }

    /**
     * Scope for active overrides
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by field name
     */
    public function scopeForField(Builder $query, string $fieldName): Builder
    {
        return $query->where('field_name', $fieldName);
    }

    /**
     * Scope by field type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('field_type', $type);
    }

    /**
     * Scope by article mapping
     */
    public function scopeForMapping(Builder $query, int $mappingId): Builder
    {
        return $query->where('cms_legacy_article_mapping_id', $mappingId);
    }
}