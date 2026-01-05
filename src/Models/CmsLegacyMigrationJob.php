<?php

namespace Westlinks\Wlcms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CmsLegacyMigrationJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cms_legacy_migration_jobs';

    protected $fillable = [
        'job_id',
        'type',
        'status',
        'started_at',
        'completed_at',
        'options',
        'progress',
        'stats',
        'error_count',
        'warning_count',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'options' => 'array',
        'progress' => 'array',
        'stats' => 'array',
        'error_count' => 'integer',
        'warning_count' => 'integer',
    ];

    /**
     * Get the duration of the migration job
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->started_at->diffForHumans($this->completed_at, true);
    }

    /**
     * Get the duration in seconds
     */
    public function getDurationSecondsAttribute(): ?float
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    /**
     * Check if the job is running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if the job is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the job has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the job was cancelled
     */
    public function wasCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the success rate of the migration
     */
    public function getSuccessRateAttribute(): float
    {
        $totalItems = $this->progress['total_items'] ?? 0;
        $successfulItems = $this->progress['successful_items'] ?? 0;

        if ($totalItems === 0) {
            return 0;
        }

        return round(($successfulItems / $totalItems) * 100, 2);
    }

    /**
     * Get the items per second rate
     */
    public function getItemsPerSecondAttribute(): float
    {
        return $this->stats['items_per_second'] ?? 0;
    }

    /**
     * Get human readable memory usage
     */
    public function getPeakMemoryFormattedAttribute(): string
    {
        $bytes = $this->stats['peak_memory'] ?? 0;
        
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $base = log($bytes, 1024);
        
        return round(pow(1024, $base - floor($base)), 2) . ' ' . $units[floor($base)];
    }

    /**
     * Get the status badge class for display
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'running' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'batch_migration' => 'Batch Migration',
            'single_item' => 'Single Item',
            'validation' => 'Data Validation',
            'cleanup' => 'Data Cleanup',
            'test_migration' => 'Test Migration',
            default => ucwords(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Scope for completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for running jobs
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope for failed jobs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for jobs by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for recent jobs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * Get jobs grouped by status
     */
    public static function getJobsByStatus(): array
    {
        return [
            'running' => static::running()->count(),
            'completed' => static::completed()->count(),
            'failed' => static::failed()->count(),
            'cancelled' => static::where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Get performance statistics
     */
    public static function getPerformanceStats(): array
    {
        $completedJobs = static::completed()->get();
        
        if ($completedJobs->isEmpty()) {
            return [
                'average_duration' => 0,
                'average_items_per_second' => 0,
                'total_items_migrated' => 0,
                'average_memory_usage' => 0,
            ];
        }

        $totalDuration = $completedJobs->sum('duration_seconds');
        $totalItemsMigrated = $completedJobs->sum(function ($job) {
            return $job->progress['successful_items'] ?? 0;
        });
        
        $totalMemoryUsage = $completedJobs->sum(function ($job) {
            return $job->stats['peak_memory'] ?? 0;
        });

        return [
            'average_duration' => round($totalDuration / $completedJobs->count(), 2),
            'average_items_per_second' => $totalDuration > 0 ? round($totalItemsMigrated / $totalDuration, 2) : 0,
            'total_items_migrated' => $totalItemsMigrated,
            'average_memory_usage' => round($totalMemoryUsage / $completedJobs->count()),
        ];
    }

    /**
     * Clean up old completed jobs
     */
    public static function cleanupOldJobs(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return static::where('started_at', '<', $cutoffDate)
            ->whereIn('status', ['completed', 'failed', 'cancelled'])
            ->delete();
    }
}