<?php

namespace Westlinks\Wlcms\Services;

use Westlinks\Wlcms\Models\CmsLegacyMigrationJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MigrationProgressService
{
    protected string $cachePrefix = 'wlcms_migration_';
    protected int $cacheTtl = 3600; // 1 hour

    /**
     * Start a new migration job
     */
    public function startJob(array $options = []): string
    {
        $jobId = $this->generateJobId();
        
        $jobData = [
            'id' => $jobId,
            'status' => 'running',
            'type' => $options['type'] ?? 'batch_migration',
            'started_at' => now()->toISOString(),
            'options' => $options,
            'progress' => [
                'total_items' => $options['total_items'] ?? 0,
                'processed_items' => 0,
                'successful_items' => 0,
                'failed_items' => 0,
                'current_batch' => 0,
                'total_batches' => $options['total_batches'] ?? 0,
                'percentage' => 0,
            ],
            'stats' => [
                'start_time' => microtime(true),
                'items_per_second' => 0,
                'estimated_completion' => null,
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true),
            ],
            'errors' => [],
            'warnings' => [],
            'logs' => [],
        ];
        
        $this->saveJobData($jobId, $jobData);
        
        // Create database record for persistence
        CmsLegacyMigrationJob::create([
            'job_id' => $jobId,
            'type' => $jobData['type'],
            'status' => 'running',
            'started_at' => now(),
            'options' => $options,
            'progress' => $jobData['progress'],
            'stats' => $jobData['stats'],
        ]);
        
        Log::info('Migration job started', ['job_id' => $jobId, 'options' => $options]);
        
        return $jobId;
    }

    /**
     * Update job progress
     */
    public function updateProgress(string $jobId, array $progress): void
    {
        $jobData = $this->getJobData($jobId);
        
        if (!$jobData) {
            return;
        }
        
        // Update progress
        $jobData['progress'] = array_merge($jobData['progress'], $progress);
        
        // Calculate percentage
        if ($jobData['progress']['total_items'] > 0) {
            $jobData['progress']['percentage'] = round(
                ($jobData['progress']['processed_items'] / $jobData['progress']['total_items']) * 100,
                2
            );
        }
        
        // Update statistics
        $this->updateStats($jobData);
        
        // Save updated data
        $this->saveJobData($jobId, $jobData);
        
        // Update database record
        $this->updateDatabaseRecord($jobId, $jobData);
    }

    /**
     * Add log entry to job
     */
    public function addLog(string $jobId, string $level, string $message, array $context = []): void
    {
        $jobData = $this->getJobData($jobId);
        
        if (!$jobData) {
            return;
        }
        
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
        
        $jobData['logs'][] = $logEntry;
        
        // Keep only recent logs (last 100 entries)
        if (count($jobData['logs']) > 100) {
            $jobData['logs'] = array_slice($jobData['logs'], -100);
        }
        
        $this->saveJobData($jobId, $jobData);
    }

    /**
     * Add error to job
     */
    public function addError(string $jobId, string $error, array $context = []): void
    {
        $jobData = $this->getJobData($jobId);
        
        if (!$jobData) {
            return;
        }
        
        $errorEntry = [
            'timestamp' => now()->toISOString(),
            'error' => $error,
            'context' => $context,
        ];
        
        $jobData['errors'][] = $errorEntry;
        $jobData['progress']['failed_items']++;
        
        $this->saveJobData($jobId, $jobData);
        $this->addLog($jobId, 'error', $error, $context);
        
        Log::error('Migration job error', [
            'job_id' => $jobId,
            'error' => $error,
            'context' => $context,
        ]);
    }

    /**
     * Add warning to job
     */
    public function addWarning(string $jobId, string $warning, array $context = []): void
    {
        $jobData = $this->getJobData($jobId);
        
        if (!$jobData) {
            return;
        }
        
        $warningEntry = [
            'timestamp' => now()->toISOString(),
            'warning' => $warning,
            'context' => $context,
        ];
        
        $jobData['warnings'][] = $warningEntry;
        
        $this->saveJobData($jobData['id'], $jobData);
        $this->addLog($jobId, 'warning', $warning, $context);
    }

    /**
     * Complete migration job
     */
    public function completeJob(string $jobId, string $status = 'completed'): void
    {
        $jobData = $this->getJobData($jobId);
        
        if (!$jobData) {
            return;
        }
        
        $jobData['status'] = $status;
        $jobData['completed_at'] = now()->toISOString();
        
        // Final statistics
        $jobData['stats']['end_time'] = microtime(true);
        $jobData['stats']['total_duration'] = $jobData['stats']['end_time'] - $jobData['stats']['start_time'];
        $jobData['stats']['peak_memory'] = memory_get_peak_usage(true);
        
        if ($jobData['progress']['processed_items'] > 0) {
            $jobData['stats']['items_per_second'] = round(
                $jobData['progress']['processed_items'] / $jobData['stats']['total_duration'],
                2
            );
        }
        
        $this->saveJobData($jobId, $jobData);
        
        // Update database record
        CmsLegacyMigrationJob::where('job_id', $jobId)->update([
            'status' => $status,
            'completed_at' => now(),
            'progress' => $jobData['progress'],
            'stats' => $jobData['stats'],
            'error_count' => count($jobData['errors']),
            'warning_count' => count($jobData['warnings']),
        ]);
        
        Log::info('Migration job completed', [
            'job_id' => $jobId,
            'status' => $status,
            'stats' => $jobData['stats'],
        ]);
    }

    /**
     * Get job data
     */
    public function getJobData(string $jobId): ?array
    {
        return Cache::get($this->cachePrefix . $jobId);
    }

    /**
     * Get job progress for display
     */
    public function getJobProgress(string $jobId): ?array
    {
        $jobData = $this->getJobData($jobId);
        
        if (!$jobData) {
            return null;
        }
        
        return [
            'id' => $jobId,
            'status' => $jobData['status'],
            'type' => $jobData['type'],
            'progress' => $jobData['progress'],
            'stats' => $jobData['stats'],
            'started_at' => $jobData['started_at'],
            'completed_at' => $jobData['completed_at'] ?? null,
            'error_count' => count($jobData['errors']),
            'warning_count' => count($jobData['warnings']),
            'recent_logs' => array_slice($jobData['logs'], -10),
        ];
    }

    /**
     * Get all active migration jobs
     */
    public function getActiveJobs(): array
    {
        $jobs = CmsLegacyMigrationJob::where('status', 'running')
            ->orderBy('started_at', 'desc')
            ->get();
        
        $activeJobs = [];
        
        foreach ($jobs as $job) {
            $jobData = $this->getJobData($job->job_id);
            if ($jobData) {
                $activeJobs[] = $this->getJobProgress($job->job_id);
            }
        }
        
        return $activeJobs;
    }

    /**
     * Get job history
     */
    public function getJobHistory(int $limit = 10): array
    {
        return CmsLegacyMigrationJob::orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->job_id,
                    'type' => $job->type,
                    'status' => $job->status,
                    'started_at' => $job->started_at->toISOString(),
                    'completed_at' => $job->completed_at?->toISOString(),
                    'duration' => $job->completed_at ? 
                        $job->completed_at->diffForHumans($job->started_at, true) : null,
                    'progress' => $job->progress,
                    'stats' => $job->stats,
                    'error_count' => $job->error_count,
                    'warning_count' => $job->warning_count,
                ];
            })
            ->toArray();
    }

    /**
     * Cancel a running job
     */
    public function cancelJob(string $jobId): bool
    {
        $jobData = $this->getJobData($jobId);
        
        if (!$jobData || $jobData['status'] !== 'running') {
            return false;
        }
        
        $this->completeJob($jobId, 'cancelled');
        
        return true;
    }

    /**
     * Clean up old job data
     */
    public function cleanupOldJobs(int $daysOld = 7): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        // Delete from database
        $deleted = CmsLegacyMigrationJob::where('started_at', '<', $cutoffDate)->delete();
        
        // Clean up cache (this is approximate since we can't easily enumerate all keys)
        $oldJobs = CmsLegacyMigrationJob::withTrashed()
            ->where('started_at', '<', $cutoffDate)
            ->pluck('job_id');
        
        foreach ($oldJobs as $jobId) {
            Cache::forget($this->cachePrefix . $jobId);
        }
        
        return $deleted;
    }

    /**
     * Generate unique job ID
     */
    protected function generateJobId(): string
    {
        return 'job_' . now()->format('Y-m-d_H-i-s') . '_' . uniqid();
    }

    /**
     * Save job data to cache
     */
    protected function saveJobData(string $jobId, array $jobData): void
    {
        Cache::put($this->cachePrefix . $jobId, $jobData, $this->cacheTtl);
    }

    /**
     * Update statistics for job
     */
    protected function updateStats(array &$jobData): void
    {
        $currentTime = microtime(true);
        $elapsedTime = $currentTime - $jobData['stats']['start_time'];
        
        if ($elapsedTime > 0 && $jobData['progress']['processed_items'] > 0) {
            $jobData['stats']['items_per_second'] = round(
                $jobData['progress']['processed_items'] / $elapsedTime,
                2
            );
            
            // Estimate completion time
            $remainingItems = $jobData['progress']['total_items'] - $jobData['progress']['processed_items'];
            if ($remainingItems > 0 && $jobData['stats']['items_per_second'] > 0) {
                $estimatedSecondsRemaining = $remainingItems / $jobData['stats']['items_per_second'];
                $jobData['stats']['estimated_completion'] = now()
                    ->addSeconds($estimatedSecondsRemaining)
                    ->toISOString();
            }
        }
        
        $jobData['stats']['memory_usage'] = memory_get_usage(true);
        $jobData['stats']['peak_memory'] = memory_get_peak_usage(true);
        $jobData['updated_at'] = now()->toISOString();
    }

    /**
     * Update database record
     */
    protected function updateDatabaseRecord(string $jobId, array $jobData): void
    {
        CmsLegacyMigrationJob::where('job_id', $jobId)->update([
            'progress' => $jobData['progress'],
            'stats' => $jobData['stats'],
            'error_count' => count($jobData['errors']),
            'warning_count' => count($jobData['warnings']),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get summary statistics for all migrations
     */
    public function getMigrationSummary(): array
    {
        $totalJobs = CmsLegacyMigrationJob::count();
        $completedJobs = CmsLegacyMigrationJob::where('status', 'completed')->count();
        $failedJobs = CmsLegacyMigrationJob::where('status', 'failed')->count();
        $runningJobs = CmsLegacyMigrationJob::where('status', 'running')->count();
        
        $totalItemsMigrated = CmsLegacyMigrationJob::where('status', 'completed')
            ->get()
            ->sum(function ($job) {
                return $job->progress['successful_items'] ?? 0;
            });
        
        $totalErrors = CmsLegacyMigrationJob::sum('error_count');
        $totalWarnings = CmsLegacyMigrationJob::sum('warning_count');
        
        return [
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'failed_jobs' => $failedJobs,
            'running_jobs' => $runningJobs,
            'total_items_migrated' => $totalItemsMigrated,
            'total_errors' => $totalErrors,
            'total_warnings' => $totalWarnings,
            'success_rate' => $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 2) : 0,
        ];
    }
}