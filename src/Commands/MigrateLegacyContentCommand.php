<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Westlinks\Wlcms\Services\LegacyDatabaseService;
use Westlinks\Wlcms\Services\DataMigrationService;
use Westlinks\Wlcms\Services\DataValidationService;
use Westlinks\Wlcms\Services\FieldTransformationService;
use Westlinks\Wlcms\Services\MigrationProgressService;
use Westlinks\Wlcms\Models\CmsLegacyArticleMapping;

class MigrateLegacyContentCommand extends Command
{
    protected $signature = 'wlcms:migrate-legacy
                            {--batch-size=25 : Number of articles to migrate per batch}
                            {--content-type=article : Content type for migrated articles}
                            {--validate-first : Run validation before migration}
                            {--dry-run : Preview migration without making changes}
                            {--force : Skip confirmation prompts}
                            {--filter-status= : Only migrate articles with specific status}
                            {--filter-category= : Only migrate articles from specific category}
                            {--date-from= : Only migrate articles from this date onwards}
                            {--date-to= : Only migrate articles up to this date}
                            {--resume-from-id= : Resume migration from specific article ID}
                            {--max-articles= : Maximum number of articles to migrate}
                            {--job-id= : Continue an existing migration job}
                            {--show-progress : Display detailed progress information}';

    protected $description = 'Migrate legacy articles to WLCMS format';

    protected LegacyDatabaseService $legacyDb;
    protected DataMigrationService $migrationService;
    protected DataValidationService $validationService;
    protected FieldTransformationService $transformer;
    protected MigrationProgressService $progressService;
    protected ?string $jobId = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🚀 WLCMS Legacy Content Migration Tool');
        $this->info('=====================================');

        // Initialize services
        $this->initializeServices();

        // Check for existing job ID
        if ($existingJobId = $this->option('job-id')) {
            return $this->resumeJob($existingJobId);
        }

        // Check legacy database connection
        if (!$this->validateLegacyConnection()) {
            return self::FAILURE;
        }

        // Run pre-migration validation if requested
        if ($this->option('validate-first')) {
            if (!$this->runValidation()) {
                return self::FAILURE;
            }
        }

        // Get migration options
        $options = $this->getMigrationOptions();

        // Show migration summary
        $this->showMigrationSummary($options);

        // Confirm migration unless forced
        if (!$this->option('force') && !$this->confirmMigration()) {
            $this->info('Migration cancelled by user.');
            return self::SUCCESS;
        }

        // Start new migration job
        $this->jobId = $this->progressService->startJob([
            'type' => 'batch_migration',
            'batch_size' => $options['batch_size'],
            'total_items' => $options['total_articles'],
            'total_batches' => ceil($options['total_articles'] / $options['batch_size']),
            'options' => $options,
        ]);

        $this->info("🎯 Started migration job: {$this->jobId}");

        // Execute migration
        return $this->executeMigration($options);
    }

    protected function initializeServices(): void
    {
        $this->legacyDb = app(LegacyDatabaseService::class);
        $this->transformer = app(FieldTransformationService::class);
        $this->migrationService = new DataMigrationService($this->legacyDb, $this->transformer);
        $this->validationService = new DataValidationService($this->legacyDb);
        $this->progressService = app(MigrationProgressService::class);
    }

    protected function validateLegacyConnection(): bool
    {
        $this->info('🔍 Checking legacy database connection...');
        
        $connectionTest = $this->legacyDb->testConnection();
        
        if ($connectionTest['status'] !== 'success') {
            $this->error('❌ Legacy database connection failed:');
            $this->error($connectionTest['message']);
            
            $this->warn('Please check your legacy database configuration in config/wlcms.php');
            return false;
        }
        
        $this->info('✅ Legacy database connection successful');
        $this->info("   Database: {$connectionTest['database']}");
        $this->info("   Tables found: {$connectionTest['table_count']}");
        
        return true;
    }

    protected function runValidation(): bool
    {
        $this->info('🔍 Running pre-migration validation...');
        
        $validationResults = $this->validationService->validateLegacyDatabase();
        
        // Display validation results
        if (!empty($validationResults['errors'])) {
            $this->error('❌ Validation Errors:');
            foreach ($validationResults['errors'] as $error) {
                $this->error("   • {$error}");
            }
        }
        
        if (!empty($validationResults['warnings'])) {
            $this->warn('⚠️  Validation Warnings:');
            foreach ($validationResults['warnings'] as $warning) {
                $this->warn("   • {$warning}");
            }
        }
        
        if (!empty($validationResults['recommendations'])) {
            $this->info('💡 Recommendations:');
            foreach ($validationResults['recommendations'] as $recommendation) {
                $this->info("   • {$recommendation}");
            }
        }
        
        if ($validationResults['status'] === 'error') {
            $this->error('❌ Validation failed. Please fix errors before proceeding.');
            return false;
        }
        
        if ($validationResults['status'] === 'warning' && !$this->option('force')) {
            return $this->confirm('⚠️  Validation completed with warnings. Continue anyway?');
        }
        
        $this->info('✅ Validation completed successfully');
        return true;
    }

    protected function getMigrationOptions(): array
    {
        $filters = [];
        
        if ($status = $this->option('filter-status')) {
            $filters['status'] = $status;
        }
        
        if ($category = $this->option('filter-category')) {
            $filters['category'] = $category;
        }
        
        if ($dateFrom = $this->option('date-from')) {
            $filters['date_from'] = $dateFrom;
        }
        
        if ($dateTo = $this->option('date-to')) {
            $filters['date_to'] = $dateTo;
        }
        
        return [
            'batch_size' => (int) $this->option('batch-size'),
            'content_type' => $this->option('content-type'),
            'dry_run' => $this->option('dry-run'),
            'resume_from_id' => $this->option('resume-from-id'),
            'max_articles' => $this->option('max-articles'),
            'filters' => $filters,
            'preserve_hierarchy' => true,
            'create_redirects' => true,
            'mapping_type' => 'migration',
            'sync_frequency' => 'manual',
            'total_articles' => $this->legacyDb->getArticleCount($filters),
        ];
    }

    protected function showMigrationSummary(array $options): void
    {
        $this->info('📋 Migration Summary:');
        
        // Get article count
        $totalArticles = $this->legacyDb->getArticleCount($options['filters']);
        $mappedArticles = CmsLegacyArticleMapping::count();
        $unmappedArticles = max(0, $totalArticles - $mappedArticles);
        
        $this->table(['Metric', 'Count'], [
            ['Total Legacy Articles', number_format($totalArticles)],
            ['Already Mapped', number_format($mappedArticles)],
            ['Unmapped Articles', number_format($unmappedArticles)],
            ['Batch Size', $options['batch_size']],
            ['Content Type', $options['content_type']],
            ['Max Articles', $options['max_articles'] ?? 'All'],
        ]);
        
        if (!empty($options['filters'])) {
            $this->info('🔍 Active Filters:');
            foreach ($options['filters'] as $filter => $value) {
                $this->info("   • {$filter}: {$value}");
            }
        }
        
        if ($options['dry_run']) {
            $this->warn('🏃‍♂️ DRY RUN MODE - No changes will be made');
        }
    }

    protected function confirmMigration(): bool
    {
        return $this->confirm('🚀 Proceed with migration?');
    }

    protected function resumeJob(string $jobId): int
    {
        $this->info("🔄 Resuming migration job: {$jobId}");
        
        $jobData = $this->progressService->getJobProgress($jobId);
        
        if (!$jobData) {
            $this->error('❌ Job not found or has expired');
            return self::FAILURE;
        }
        
        if ($jobData['status'] !== 'running') {
            $this->error("❌ Job is not in running state. Current status: {$jobData['status']}");
            return self::FAILURE;
        }
        
        $this->jobId = $jobId;
        $this->info('✅ Job resumed successfully');
        
        // Continue with existing job options
        $options = $jobData['progress']['options'] ?? $this->getMigrationOptions();
        
        return $this->executeMigration($options);
    }

    protected function executeMigration(array $options): int
    {
        $this->info('🚀 Starting migration...');
        
        $totalMigrated = 0;
        $totalErrors = 0;
        $batchNumber = 1;
        $maxArticles = $options['max_articles'];
        
        try {
            // Create progress bar
            $unmappedCount = $this->legacyDb->getArticleCount($options['filters']) - CmsLegacyArticleMapping::count();
            $progressBar = $this->output->createProgressBar($unmappedCount);
            $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            
            while (true) {
                // Check if we've hit the max articles limit
                if ($maxArticles && $totalMigrated >= $maxArticles) {
                    $this->info("\n📊 Reached maximum article limit ({$maxArticles})");
                    break;
                }
                
                // Calculate remaining batch size
                $batchSize = $options['batch_size'];
                if ($maxArticles) {
                    $remaining = $maxArticles - $totalMigrated;
                    $batchSize = min($batchSize, $remaining);
                }
                
                // Get batch options
                $batchOptions = array_merge($options, ['batch_size' => $batchSize]);
                
                if ($options['dry_run']) {
                    $result = $this->simulateBatch($batchOptions);
                } else {
                    $result = $this->migrationService->migrateBatch($batchOptions);
                }
                
                // Update progress tracking
                if ($this->jobId) {
                    $this->progressService->updateProgress($this->jobId, [
                        'processed_items' => $totalMigrated + $result['stats']['success'],
                        'successful_items' => $totalMigrated + $result['stats']['success'],
                        'failed_items' => $totalErrors + $result['stats']['errors'],
                        'current_batch' => $batchNumber,
                    ]);
                    
                    // Log any errors
                    foreach ($result['stats']['error_details'] ?? [] as $error) {
                        $this->progressService->addError($this->jobId, $error['error'], [
                            'article_id' => $error['article_id'],
                            'batch' => $batchNumber,
                        ]);
                    }
                    
                    // Add batch completion log
                    $this->progressService->addLog($this->jobId, 'info', 
                        "Batch {$batchNumber} completed: {$result['stats']['success']} success, {$result['stats']['errors']} errors"
                    );
                }
                
                // Update progress bar
                $progressBar->advance($result['stats']['total']);
                
                // Update counters
                $totalMigrated += $result['stats']['success'];
                $totalErrors += $result['stats']['errors'];
                
                // Log batch results
                $this->logBatchResults($batchNumber, $result);
                
                // Show detailed progress if requested
                if ($this->option('show-progress')) {
                    $this->showProgressDetails($batchNumber, $totalMigrated, $totalErrors);
                }
                
                // Check if we're done
                if ($result['stats']['total'] < $batchSize) {
                    break; // No more articles to process
                }
                
                $batchNumber++;
                
                // Small delay to prevent overwhelming the database
                usleep(100000); // 0.1 seconds
            }
            
            $progressBar->finish();
            
            // Show final results
            $this->showFinalResults($totalMigrated, $totalErrors, $options['dry_run']);
            
            // Complete job tracking
            if ($this->jobId) {
                $status = $totalErrors > 0 ? 'completed_with_errors' : 'completed';
                $this->progressService->completeJob($this->jobId, $status);
                
                $this->info("📊 Migration job {$this->jobId} completed with status: {$status}");
            }
            
            return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
            
        } catch (\Exception $e) {
            if ($this->jobId) {
                $this->progressService->addError($this->jobId, $e->getMessage());
                $this->progressService->completeJob($this->jobId, 'failed');
            }
            
            $this->error('❌ Migration failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function simulateBatch(array $options): array
    {
        // Simulate migration for dry run
        $articles = $this->legacyDb->getUnmappedArticles($options['batch_size']);
        
        $stats = [
            'total' => $articles->count(),
            'success' => $articles->count(),
            'errors' => 0,
            'error_details' => [],
        ];
        
        return [
            'status' => 'simulated',
            'message' => "Would migrate {$stats['total']} articles",
            'stats' => $stats,
        ];
    }

    protected function logBatchResults(int $batchNumber, array $result): void
    {
        $stats = $result['stats'];
        
        if ($stats['errors'] > 0) {
            $this->warn("\nBatch {$batchNumber}: {$stats['success']} success, {$stats['errors']} errors");
            
            // Show error details if verbose
            if ($this->option('verbose')) {
                foreach ($stats['error_details'] as $error) {
                    $this->error("   Article {$error['article_id']}: {$error['error']}");
                }
            }
        }
    }

    protected function showFinalResults(int $totalMigrated, int $totalErrors, bool $dryRun): void
    {
        $this->info("\n\n🎉 Migration Complete!");
        $this->info('========================');
        
        if ($dryRun) {
            $this->info("🏃‍♂️ DRY RUN - Would have migrated {$totalMigrated} articles");
        } else {
            $this->info("✅ Successfully migrated: {$totalMigrated} articles");
        }
        
        if ($totalErrors > 0) {
            $this->error("❌ Errors encountered: {$totalErrors}");
            $this->warn("Check the logs for detailed error information.");
        }
        
        if (!$dryRun && $totalMigrated > 0) {
            $this->info("📊 View migrated content at: " . route('wlcms.admin.legacy.index'));
        }
    }

    protected function showProgressDetails(int $batchNumber, int $totalMigrated, int $totalErrors): void
    {
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        
        $this->table(['Metric', 'Value'], [
            ['Current Batch', $batchNumber],
            ['Total Migrated', number_format($totalMigrated)],
            ['Total Errors', number_format($totalErrors)],
            ['Memory Usage', "{$memoryUsage} MB"],
            ['Peak Memory', "{$peakMemory} MB"],
        ]);
        
        if ($this->jobId) {
            $progress = $this->progressService->getJobProgress($this->jobId);
            if ($progress && isset($progress['stats']['items_per_second'])) {
                $this->info("⚡ Processing rate: {$progress['stats']['items_per_second']} items/second");
                
                if (isset($progress['stats']['estimated_completion'])) {
                    $this->info("🕒 Estimated completion: {$progress['stats']['estimated_completion']}");
                }
            }
        }
    }
}