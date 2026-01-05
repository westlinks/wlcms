<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CmsLegacyArticleMapping extends Model
{
    use HasFactory;

    protected $table = 'cms_legacy_article_mappings';

    protected $fillable = [
        'cms_content_item_id',
        'legacy_article_id',
        'mapping_type',
        'sort_order',
        'is_active',
        'sync_frequency',
        'field_mappings',
        'last_sync_at',
        'sync_error',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'field_mappings' => 'array',
        'last_sync_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the CMS content item associated with this mapping
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class, 'cms_content_item_id');
    }

    /**
     * Get the field overrides for this mapping
     */
    public function fieldOverrides(): HasMany
    {
        return $this->hasMany(CmsLegacyFieldOverride::class, 'cms_legacy_article_mapping_id');
    }

    /**
     * Get active field overrides
     */
    public function activeFieldOverrides(): HasMany
    {
        return $this->fieldOverrides()->where('is_active', true);
    }

    /**
     * Get the legacy article instance
     */
    public function getLegacyArticle()
    {
        $modelClass = config('wlcms.legacy.article_model');
        
        if (!class_exists($modelClass)) {
            return null;
        }
        
        return $modelClass::find($this->legacy_article_id);
    }

    /**
     * Get effective article data with field overrides applied
     */
    public function getEffectiveArticleData(): array
    {
        $legacyArticle = $this->getLegacyArticle();
        
        if (!$legacyArticle) {
            return [];
        }
        
        // Start with legacy article data
        $data = $legacyArticle->toArray();
        
        // Apply field overrides
        foreach ($this->activeFieldOverrides as $override) {
            $value = $this->castFieldValue($override->override_value, $override->field_type);
            $data[$override->field_name] = $value;
        }
        
        return $data;
    }

    /**
     * Cast field value to appropriate type
     */
    protected function castFieldValue($value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json' => json_decode($value, true),
            'datetime' => $value ? new \Carbon\Carbon($value) : null,
            'text', 'string' => $value,
            default => $value,
        };
    }

    /**
     * Scope for active mappings
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by mapping type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('mapping_type', $type);
    }

    /**
     * Scope by content item
     */
    public function scopeForContentItem(Builder $query, int $contentItemId): Builder
    {
        return $query->where('cms_content_item_id', $contentItemId);
    }
}