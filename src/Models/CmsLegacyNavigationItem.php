<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CmsLegacyNavigationItem extends Model
{
    use HasFactory;

    protected $table = 'cms_legacy_navigation_items';

    protected $fillable = [
        'cms_content_item_id',
        'navigation_context',
        'parent_id',
        'sort_order',
        'label',
        'slug',
        'css_class',
        'icon',
        'is_active',
        'show_in_menu',
        'target',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the content item associated with this navigation item
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class, 'cms_content_item_id');
    }

    /**
     * Get the parent navigation item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child navigation items
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get active child navigation items
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /**
     * Get all descendants recursively
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get ancestors up to root
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;
        
        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }
        
        return $ancestors;
    }

    /**
     * Get the breadcrumb trail
     */
    public function getBreadcrumbTrail(): array
    {
        $trail = $this->ancestors()->toArray();
        $trail[] = $this->toArray();
        
        return $trail;
    }

    /**
     * Check if this item has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this item has active children
     */
    public function hasActiveChildren(): bool
    {
        return $this->activeChildren()->exists();
    }

    /**
     * Get the effective label (falls back to content item title)
     */
    public function getEffectiveLabel(): string
    {
        return $this->label ?: $this->contentItem->title;
    }

    /**
     * Get the effective slug (falls back to content item slug)
     */
    public function getEffectiveSlug(): string
    {
        return $this->slug ?: $this->contentItem->slug;
    }

    /**
     * Scope for active items
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for menu-visible items
     */
    public function scopeInMenu(Builder $query): Builder
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope by navigation context
     */
    public function scopeInContext(Builder $query, string $context): Builder
    {
        return $query->where('navigation_context', $context);
    }

    /**
     * Scope for root items (no parent)
     */
    public function scopeRootItems(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope by parent
     */
    public function scopeChildrenOf(Builder $query, ?int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}