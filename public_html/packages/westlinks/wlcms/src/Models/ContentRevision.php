<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentRevision extends Model
{
    use HasFactory;

    protected $table = 'cms_content_revisions';

    protected $fillable = [
        'content_id',
        'user_id',
        'content_data',
        'revision_note',
        'is_autosave',
    ];

    protected $casts = [
        'content_data' => 'array',
        'is_autosave' => 'boolean',
    ];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (ContentRevision $revision) {
            $revision->created_at = now();
        });
    }

    /**
     * Get the content item this revision belongs to.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class, 'content_id');
    }

    /**
     * Get the user who created this revision.
     */
    public function user(): BelongsTo
    {
        $userModel = config('wlcms.user.model', \App\Models\User::class);
        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Restore this revision to the content item.
     */
    public function restore(): bool
    {
        $contentItem = $this->contentItem;
        
        if (!$contentItem) {
            return false;
        }

        // Create a new revision before restoring
        $contentItem->createRevision('Restored from revision ' . $this->id);

        // Update the content item with this revision's data
        $contentItem->fill($this->content_data);
        
        return $contentItem->save();
    }

    /**
     * Get the differences between this revision and another.
     */
    public function getDifferences(ContentRevision $otherRevision): array
    {
        $thisData = $this->content_data;
        $otherData = $otherRevision->content_data;
        
        $differences = [];
        
        foreach ($thisData as $key => $value) {
            if (!isset($otherData[$key]) || $otherData[$key] !== $value) {
                $differences[$key] = [
                    'old' => $otherData[$key] ?? null,
                    'new' => $value,
                ];
            }
        }
        
        return $differences;
    }
}