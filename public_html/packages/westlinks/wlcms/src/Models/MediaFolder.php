<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MediaFolder extends Model
{
    use HasFactory;

    protected $table = 'cms_media_folders';

    protected $fillable = [
        'name',
        'parent_id',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    /**
     * Scope for ordered folders.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('name');
    }

    /**
     * Scope for top-level folders (no parent).
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    /**
     * Get the child folders.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id')->ordered();
    }

    /**
     * Get the media assets in this folder.
     */
    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class, 'folder_id');
    }

    /**
     * Get the full path of the folder.
     */
    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_path . '/' . $this->name;
        }

        return $this->name;
    }

    /**
     * Get all descendant folders.
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id')->with('descendants');
    }

    /**
     * Get the total number of media assets in this folder and its descendants.
     */
    public function getTotalAssetsCountAttribute(): int
    {
        $count = $this->mediaAssets()->count();
        
        foreach ($this->children as $child) {
            $count += $child->total_assets_count;
        }
        
        return $count;
    }
}