<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_form_submissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'form_identifier',
        'form_name',
        'data',
        'ip_address',
        'user_agent',
        'status',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'submitted_at' => 'datetime',
    ];

    /**
     * Scope to filter unread submissions.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope to filter by form identifier.
     */
    public function scopeForForm($query, string $identifier)
    {
        return $query->where('form_identifier', $identifier);
    }

    /**
     * Mark submission as read.
     */
    public function markAsRead(): void
    {
        $this->update(['status' => 'read']);
    }

    /**
     * Mark submission as archived.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Check if submission is unread.
     */
    public function isUnread(): bool
    {
        return $this->status === 'unread';
    }
}
