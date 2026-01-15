<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Westlinks\Wlcms\Services\UserService;

class ContentItem extends Model
{
    use HasFactory;

    protected $table = 'cms_content_items';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'type',
        'status',
        'meta',
        'parent_id',
        'sort_order',
        'is_featured',
        'show_in_menu',
        'menu_title',
        'menu_order',
        'menu_location',
        'published_at',
        'user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'menu_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContentItem $item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->title);
            }
            
            // Only set user fields if user integration is enabled and user is authenticated
            if (UserService::isUserIntegrationEnabled() && auth()->check()) {
                $item->created_by = auth()->id();
                $item->updated_by = auth()->id();
            }
        });

        static::updating(function (ContentItem $item) {
            // Only set user fields if user integration is enabled and user is authenticated
            if (UserService::isUserIntegrationEnabled() && auth()->check()) {
                $item->updated_by = auth()->id();
            }
        });
    }

    /**
     * Scope for published content items.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope for ordered content items.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Scope for top-level content items (no parent).
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to find content by slug.
     */
    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope to filter content by type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Get the parent content item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class, 'parent_id');
    }

    /**
     * Get the child content items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ContentItem::class, 'parent_id')->ordered();
    }

    /**
     * Get the media assets used in this content.
     */
    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'cms_content_media', 'content_id', 'media_id')
            ->withTimestamps();
    }

    /**
     * Get the revisions for this content item.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class, 'content_id')->latest();
    }

    /**
     * Get the legacy article mappings for this content item.
     */
    public function legacyArticleMappings(): HasMany
    {
        return $this->hasMany(CmsLegacyArticleMapping::class, 'cms_content_item_id');
    }

    /**
     * Get the primary legacy article mapping for this content item.
     * This returns a proper Laravel relationship.
     */
    public function legacyMapping(): HasOne
    {
        return $this->hasOne(CmsLegacyArticleMapping::class, 'cms_content_item_id')
            ->where('is_active', true)
            ->latest();
    }

    /**
     * Get the active legacy article mappings.
     */
    public function activeLegacyMappings(): HasMany
    {
        return $this->legacyArticleMappings()->where('is_active', true);
    }

    /**
     * Get the legacy navigation items for this content item.
     */
    public function legacyNavigationItems(): HasMany
    {
        return $this->hasMany(CmsLegacyNavigationItem::class, 'cms_content_item_id');
    }

    /**
     * Get active legacy navigation items.
     */
    public function activeLegacyNavigationItems(): HasMany
    {
        return $this->legacyNavigationItems()->where('is_active', true);
    }

    /**
     * Get the associated legacy article (if any).
     */
    public function getLegacyArticle()
    {
        if (!config('wlcms.legacy.enabled', false)) {
            return null;
        }

        $mapping = $this->activeLegacyMappings()->first();
        if ($mapping && class_exists(config('wlcms.legacy.article_model'))) {
            return $mapping->getLegacyArticle();
        }
        
        return null;
    }

    /**
     * Check if this content item has legacy mappings.
     */
    public function hasLegacyMapping(): bool
    {
        return config('wlcms.legacy.enabled', false) && 
               $this->activeLegacyMappings()->exists();
    }

    /**
     * Get effective content data with legacy overrides applied.
     */
    public function getEffectiveContentData(): array
    {
        $data = $this->toArray();

        if (config('wlcms.legacy.enabled', false)) {
            $mapping = $this->activeLegacyMappings()->first();
            if ($mapping) {
                $legacyData = $mapping->getEffectiveArticleData();
                // Merge legacy data with CMS data (CMS takes precedence)
                $data = array_merge($legacyData, $data);
            }
        }

        return $data;
    }

    /**
     * Get the user who created this content.
     */
    public function creator(): BelongsTo
    {
        $userModel = UserService::getUserModelClass();
        
        // If no user model configured, return a null relationship
        if (!$userModel) {
            return $this->belongsTo(self::class, 'created_by')->whereRaw('1 = 0'); // Always empty
        }
        
        return $this->belongsTo($userModel, 'created_by');
    }

    /**
     * Get the user who last updated this content.
     */
    public function updater(): BelongsTo
    {
        $userModel = UserService::getUserModelClass();
        
        // If no user model configured, return a null relationship
        if (!$userModel) {
            return $this->belongsTo(self::class, 'updated_by')->whereRaw('1 = 0'); // Always empty
        }
        
        return $this->belongsTo($userModel, 'updated_by');
    }

    /**
     * Alias for creator relationship (for backwards compatibility).
     */
    public function author(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Get the creator's display name.
     */
    public function getCreatorNameAttribute(): string
    {
        return UserService::getDisplayName($this->creator);
    }

    /**
     * Get the updater's display name.
     */
    public function getUpdaterNameAttribute(): string
    {
        return UserService::getDisplayName($this->updater);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the full URL path for this content item.
     */
    public function getUrlAttribute(): string
    {
        if ($this->parent) {
            return '/' . $this->parent->slug . '/' . $this->slug;
        }

        return '/' . $this->slug;
    }

    /**
     * Get the available templates.
     */
    public static function getAvailableTemplates(): array
    {
        return config('wlcms.content.templates', [
            'default' => 'Default Page',
        ]);
    }

    /**
     * Get navigation items for a specific location.
     */
    public static function getNavigationItems(string $location = 'primary'): \Illuminate\Support\Collection
    {
        return static::where('show_in_menu', true)
            ->where('menu_location', $location)
            ->published()
            ->orderBy('menu_order')
            ->orderBy('title')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->menu_title ?: $item->title,
                    'url' => "/" . $item->slug,
                    'order' => $item->menu_order,
                    'parent_id' => $item->parent_id,
                    'location' => $item->menu_location,
                ];
            });
    }

    /**
     * Build a hierarchical navigation tree from flat navigation items.
     */
    public static function buildNavigationTree(\Illuminate\Support\Collection $items): \Illuminate\Support\Collection
    {
        // Group items by parent_id
        $grouped = $items->groupBy('parent_id');
        
        // Get top-level items (parent_id is null)
        $tree = $grouped->get(null, collect());
        
        // Recursively build children
        $tree = $tree->map(function ($item) use ($grouped) {
            return static::buildNavigationNode($item, $grouped);
        });
        
        return $tree;
    }

    /**
     * Recursively build a navigation node with its children.
     */
    private static function buildNavigationNode(array $item, \Illuminate\Support\Collection $grouped): array
    {
        $item['children'] = collect();
        
        if ($grouped->has($item['id'])) {
            $item['children'] = $grouped->get($item['id'])->map(function ($child) use ($grouped) {
                return static::buildNavigationNode($child, $grouped);
            });
        }
        
        return $item;
    }

    /**
     * Scope for items shown in navigation menus.
     */
    public function scopeInMenu(Builder $query, string $location = 'primary'): Builder
    {
        return $query->where('show_in_menu', true)
            ->where('menu_location', $location)
            ->orderBy('menu_order')
            ->orderBy('title');
    }

    /**
     * Get the effective menu title for this item.
     */
    public function getEffectiveMenuTitle(): string
    {
        return $this->menu_title ?: $this->title;
    }

    /**
     * Check if the content item is published.
     */
    public function isPublished(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the content item has revisions.
     */
    public function hasRevisions(): bool
    {
        return $this->revisions()->exists();
    }
}