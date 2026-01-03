<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;

    protected $table = 'cms_media_assets';

    protected $fillable = [
        'filename',
        'original_filename',
        'path',
        'disk',
        'mime_type',
        'filesize',
        'width',
        'height',
        'alt_text',
        'description',
        'caption',
        'credit',
        'tags',
        'folder_id',
        'uploaded_by',
    ];

    protected $casts = [
        'filesize' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'tags' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (MediaAsset $asset) {
            if (auth()->check()) {
                $asset->uploaded_by = auth()->id();
            }
        });
    }

    /**
     * Scope for images only.
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope for documents only.
     */
    public function scopeDocuments(Builder $query): Builder
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Scope for audio files only.
     */
    public function scopeAudio(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'audio/%');
    }

    /**
     * Scope for video files only.
     */
    public function scopeVideo(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    /**
     * Get the folder this media asset belongs to.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * Get the content items that use this media asset.
     */
    public function contentItems(): BelongsToMany
    {
        return $this->belongsToMany(ContentItem::class, 'cms_content_media', 'media_id', 'content_id')
            ->withTimestamps();
    }

    /**
     * Get the user who uploaded this media asset.
     */
    public function uploader(): BelongsTo
    {
        $userModel = config('wlcms.user.model', \App\Models\User::class);
        return $this->belongsTo($userModel, 'uploaded_by');
    }

    /**
     * Get the full URL to the media asset.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the human-readable file size.
     */
    public function getFilesizeHumanAttribute(): string
    {
        $bytes = $this->filesize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the media asset is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the media asset is a document.
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);
    }

    /**
     * Check if the media asset is audio.
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Check if the media asset is video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Get the thumbnail URL for the media asset.
     */
    public function getThumbnailUrl(string $size = 'medium'): ?string
    {
        if (!$this->isImage()) {
            return null;
        }

        $thumbnailPath = $this->getThumbnailPath($size);
        
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            return Storage::disk($this->disk)->url($thumbnailPath);
        }

        return $this->url; // Return original if thumbnail doesn't exist
    }

    /**
     * Get the thumbnail path for the media asset.
     */
    public function getThumbnailPath(string $size = 'medium'): string
    {
        $pathInfo = pathinfo($this->path);
        return $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
    }
}