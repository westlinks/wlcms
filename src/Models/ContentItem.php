<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
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
        'published_at',
        'user_id',
        'created_by',
        'updated_by',
    ];
        'published_at',
        'meta_description',
        'meta_data',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
        'meta_data' => 'array',
        'sort' => 'integer',
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
        return $query->where('published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope for top-level content items (no parent).
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for ordered content items.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('title');
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
     * Check if the content item is published.
     */
    public function isPublished(): bool
    {
        if (!$this->published) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }
}