<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Westlinks\Wlcms\Http\Requests\ArticleMappingRequest;
use Westlinks\Wlcms\Http\Requests\FieldOverrideRequest;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\CmsLegacyArticleMapping;
use Westlinks\Wlcms\Models\CmsLegacyFieldOverride;
use Westlinks\Wlcms\Models\CmsLegacyNavigationItem;
use Westlinks\Wlcms\Services\LegacyIntegrationService;

class LegacyController extends Controller
{
    protected LegacyIntegrationService $legacyService;

    public function __construct(LegacyIntegrationService $legacyService)
    {
        $this->legacyService = $legacyService;
        
        // Ensure legacy integration is enabled
        if (!config('wlcms.legacy.enabled')) {
            abort(404, 'Legacy integration is not enabled');
        }
    }

    /**
     * Show legacy integration dashboard
     */
    public function index()
    {
        $stats = [
            'mappings' => CmsLegacyArticleMapping::count(),
            'overrides' => CmsLegacyFieldOverride::count(),
            'navigation' => CmsLegacyNavigationItem::count(),
            'legacy_articles' => $this->getLegacyArticleCount(),
            'cms_content' => ContentItem::count(),
        ];

        $recentMappings = CmsLegacyArticleMapping::with(['contentItem', 'fieldOverrides'])
            ->latest()
            ->take(10)
            ->get();

        return view('wlcms::admin.legacy.index', compact('stats', 'recentMappings'));
    }

    /**
     * List all article mappings
     */
    public function mappings(Request $request)
    {
        $query = CmsLegacyArticleMapping::with(['contentItem', 'fieldOverrides']);

        if ($request->filled('search')) {
            $query->whereHas('contentItem', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $mappings = $query->latest()->paginate(20);

        return view('wlcms::admin.legacy.mappings.index', compact('mappings'));
    }

    /**
     * Create new article mapping
     */
    public function createMapping(Request $request)
    {
        $legacyArticles = $this->getUnmappedLegacyArticles();
        $cmsContent = ContentItem::whereDoesntHave('legacyMapping')->get();

        return view('wlcms::admin.legacy.mappings.create', compact('legacyArticles', 'cmsContent'));
    }

    /**
     * Store new article mapping
     */
    public function storeMapping(ArticleMappingRequest $request)
    {
        $validated = $request->validated();
        
        // Map cms_content_id to cms_content_item_id for database
        if (isset($validated['cms_content_id'])) {
            $validated['cms_content_item_id'] = $validated['cms_content_id'];
            unset($validated['cms_content_id']);
        }

        $mapping = CmsLegacyArticleMapping::create($validated);

        // Create field overrides if provided
        if ($request->filled('field_mappings')) {
            foreach ($request->field_mappings as $field => $override) {
                if (!empty($override)) {
                    CmsLegacyFieldOverride::create([
                        'legacy_mapping_id' => $mapping->id,
                        'field_name' => $field,
                        'override_value' => $override,
                        'data_type' => 'string', // Default type
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('wlcms.admin.legacy.mappings.index')
            ->with('success', 'Article mapping created successfully');
    }

    /**
     * Edit article mapping
     */
    public function editMapping(CmsLegacyArticleMapping $mapping)
    {
        $mapping->load(['contentItem', 'fieldOverrides']);
        $legacyArticle = $this->getLegacyArticle($mapping->legacy_article_id);
        
        return view('wlcms::admin.legacy.mappings.edit', compact('mapping', 'legacyArticle'));
    }

    /**
     * Update article mapping
     */
    public function updateMapping(ArticleMappingRequest $request, CmsLegacyArticleMapping $mapping)
    {
        $validated = $request->validated();

        $mapping->update($validated);

        // Update field overrides
        $mapping->fieldOverrides()->delete();
        if ($request->filled('field_mappings')) {
            foreach ($request->field_mappings as $field => $override) {
                if (!empty($override)) {
                    CmsLegacyFieldOverride::create([
                        'legacy_mapping_id' => $mapping->id,
                        'field_name' => $field,
                        'override_value' => $override,
                        'data_type' => 'string',
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('wlcms.admin.legacy.mappings.index')
            ->with('success', 'Article mapping updated successfully');
    }

    /**
     * Delete article mapping
     */
    public function destroyMapping(CmsLegacyArticleMapping $mapping)
    {
        $mapping->fieldOverrides()->delete();
        $mapping->delete();

        return redirect()->route('wlcms.admin.legacy.mappings.index')
            ->with('success', 'Article mapping deleted successfully');
    }

    /**
     * Sync specific mapping
     */
    public function syncMapping(CmsLegacyArticleMapping $mapping)
    {
        try {
            $result = $this->legacyService->syncArticle($mapping);
            
            return redirect()->back()
                ->with('success', "Sync completed: {$result['status']}");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk sync all mappings
     */
    public function bulkSync(Request $request)
    {
        $validated = $request->validate([
            'mapping_ids' => 'required|array',
            'mapping_ids.*' => 'exists:cms_legacy_article_mappings,id',
        ]);

        $results = [];
        foreach ($validated['mapping_ids'] as $mappingId) {
            $mapping = CmsLegacyArticleMapping::find($mappingId);
            try {
                $result = $this->legacyService->syncArticle($mapping);
                $results[] = "Mapping {$mappingId}: {$result['status']}";
            } catch (\Exception $e) {
                $results[] = "Mapping {$mappingId}: Error - {$e->getMessage()}";
            }
        }

        return redirect()->back()
            ->with('success', 'Bulk sync completed. Results: ' . implode('; ', $results));
    }

    /**
     * Navigation management
     */
    public function navigation()
    {
        $navigationItems = CmsLegacyNavigationItem::with(['parent', 'children', 'contentItem'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('wlcms::admin.legacy.navigation.index', compact('navigationItems'));
    }

    /**
     * Migration status and tools
     */
    public function migration()
    {
        $stats = [
            'legacy_articles' => $this->getLegacyArticleCount(),
            'mapped_articles' => CmsLegacyArticleMapping::count(),
            'unmapped_articles' => max(0, $this->getLegacyArticleCount() - CmsLegacyArticleMapping::count()),
            'sync_errors' => CmsLegacyArticleMapping::where('status', 'error')->count(),
        ];

        return view('wlcms::admin.legacy.migration.index', compact('stats'));
    }

    /**
     * Get legacy article count from configured model/table
     */
    protected function getLegacyArticleCount(): int
    {
        try {
            $table = config('wlcms.legacy.article_table', 'articles');
            return DB::table($table)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get unmapped legacy articles
     */
    protected function getUnmappedLegacyArticles()
    {
        try {
            $table = config('wlcms.legacy.article_table', 'articles');
            $mappedIds = CmsLegacyArticleMapping::pluck('legacy_article_id');
            
            return DB::table($table)
                ->whereNotIn('id', $mappedIds)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get specific legacy article
     */
    protected function getLegacyArticle(int $id)
    {
        try {
            $table = config('wlcms.legacy.article_table', 'articles');
            return DB::table($table)->where('id', $id)->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Show migration tools interface
     */
    public function migrationIndex()
    {
        $stats = [
            'legacy_articles' => $this->calculateLegacyArticleCount(),
            'mapped_articles' => CmsLegacyArticleMapping::where('is_active', true)->count(),
            'unmapped_articles' => $this->calculateUnmappedArticleCount(),
            'sync_errors' => CmsLegacyArticleMapping::whereNotNull('sync_error')->count(),
        ];

        return view('wlcms::admin.legacy.migration.index', compact('stats'));
    }

    /**
     * Show navigation management interface
     */
    public function navigationIndex()
    {
        $navigationItems = CmsLegacyNavigationItem::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('wlcms::admin.legacy.navigation.index', compact('navigationItems'));
    }

    /**
     * Show create navigation form
     */
    public function navigationCreate()
    {
        $parentOptions = CmsLegacyNavigationItem::whereNull('parent_id')
            ->orWhere('parent_id', 0)
            ->orderBy('title')
            ->get();

        return view('wlcms::admin.legacy.navigation.create', compact('parentOptions'));
    }

    /**
     * Bulk migrate unmapped articles
     */
    public function bulkMigrate(Request $request)
    {
        $request->validate([
            'batch_size' => 'required|integer|min:1|max:100',
            'content_type' => 'required|string',
            'preserve_hierarchy' => 'boolean',
            'create_redirects' => 'boolean',
        ]);

        try {
            $batchSize = $request->input('batch_size', 25);
            $contentType = $request->input('content_type', 'article');
            
            // Get unmapped legacy articles
            $unmappedArticles = $this->getUnmappedLegacyArticles()->take($batchSize);
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($unmappedArticles as $legacyArticle) {
                try {
                    $this->createMappingFromLegacyArticle($legacyArticle, $contentType, $request->boolean('preserve_hierarchy'));
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error('Bulk migration error for article ' . $legacyArticle->id . ': ' . $e->getMessage());
                }
            }
            
            $message = "Bulk migration completed. {$successCount} articles migrated successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }
            
            return redirect()->route('wlcms.admin.legacy.migration.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->route('wlcms.admin.legacy.migration.index')
                ->with('error', 'Bulk migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync all active mappings
     */
    public function syncAllMappings()
    {
        try {
            $mappings = CmsLegacyArticleMapping::where('is_active', true)->get();
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($mappings as $mapping) {
                try {
                    $this->legacyService->syncArticle($mapping);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $mapping->update([
                        'sync_error' => $e->getMessage(),
                        'last_sync_at' => now(),
                    ]);
                }
            }
            
            $message = "Sync completed. {$successCount} mappings synchronized successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }
            
            return redirect()->route('wlcms.admin.legacy.migration.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->route('wlcms.admin.legacy.migration.index')
                ->with('error', 'Sync operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Export mappings data
     */
    public function exportMappings(Request $request)
    {
        $format = $request->input('format', 'csv');
        $mappings = CmsLegacyArticleMapping::with(['contentItem', 'fieldOverrides'])->get();
        
        $filename = 'legacy_mappings_' . date('Y-m-d_H-i-s') . '.' . $format;
        
        if ($format === 'csv') {
            return $this->exportMappingsAsCSV($mappings, $filename);
        } elseif ($format === 'json') {
            return $this->exportMappingsAsJSON($mappings, $filename);
        }
        
        return redirect()->back()->with('error', 'Invalid export format.');
    }

    /**
     * Calculate legacy article count
     */
    private function calculateLegacyArticleCount(): int
    {
        try {
            $table = config('wlcms.legacy.article_table', 'articles');
            return DB::table($table)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate unmapped article count
     */
    private function calculateUnmappedArticleCount(): int
    {
        $totalLegacy = $this->calculateLegacyArticleCount();
        $mapped = CmsLegacyArticleMapping::where('is_active', true)->count();
        return max(0, $totalLegacy - $mapped);
    }

    /**
     * Create mapping from legacy article
     */
    private function createMappingFromLegacyArticle($legacyArticle, string $contentType, bool $preserveHierarchy): CmsLegacyArticleMapping
    {
        // Create CMS content item
        $contentData = [
            'title' => $legacyArticle->title ?? 'Imported Article',
            'content' => $legacyArticle->content ?? '',
            'type' => $contentType,
            'status' => 'published',
            'slug' => $this->generateSlugFromLegacyArticle($legacyArticle),
        ];

        $contentItem = \Westlinks\Wlcms\Models\ContentItem::create($contentData);

        // Create mapping
        return CmsLegacyArticleMapping::create([
            'legacy_article_id' => $legacyArticle->id,
            'cms_content_id' => $contentItem->id,
            'is_active' => true,
            'sync_frequency' => 'manual',
            'field_mappings' => $this->generateDefaultFieldMappings($legacyArticle),
        ]);
    }

    /**
     * Generate slug from legacy article
     */
    private function generateSlugFromLegacyArticle($legacyArticle): string
    {
        $title = $legacyArticle->title ?? $legacyArticle->slug ?? 'article-' . $legacyArticle->id;
        return \Illuminate\Support\Str::slug($title);
    }

    /**
     * Generate default field mappings
     */
    private function generateDefaultFieldMappings($legacyArticle): array
    {
        return [
            'title' => ['source' => 'title', 'type' => 'string'],
            'content' => ['source' => 'content', 'type' => 'text'],
            'summary' => ['source' => 'excerpt', 'type' => 'text'],
            'published_at' => ['source' => 'created_at', 'type' => 'datetime'],
        ];
    }

    /**
     * Export mappings as CSV
     */
    private function exportMappingsAsCSV($mappings, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($mappings) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'Legacy Article ID', 'CMS Content ID', 'Title', 'Status', 
                'Last Sync', 'Created', 'Updated'
            ]);
            
            foreach ($mappings as $mapping) {
                fputcsv($file, [
                    $mapping->id,
                    $mapping->legacy_article_id,
                    $mapping->cms_content_id,
                    $mapping->contentItem->title ?? 'N/A',
                    $mapping->is_active ? 'Active' : 'Inactive',
                    $mapping->last_sync_at?->format('Y-m-d H:i:s') ?? 'Never',
                    $mapping->created_at->format('Y-m-d H:i:s'),
                    $mapping->updated_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export mappings as JSON
     */
    private function exportMappingsAsJSON($mappings, string $filename)
    {
        $data = [
            'export_date' => now()->toISOString(),
            'total_mappings' => $mappings->count(),
            'mappings' => $mappings->map(function ($mapping) {
                return [
                    'id' => $mapping->id,
                    'legacy_article_id' => $mapping->legacy_article_id,
                    'cms_content_id' => $mapping->cms_content_id,
                    'title' => $mapping->contentItem->title ?? null,
                    'is_active' => $mapping->is_active,
                    'sync_frequency' => $mapping->sync_frequency,
                    'last_sync_at' => $mapping->last_sync_at?->toISOString(),
                    'field_overrides' => $mapping->fieldOverrides->map(function ($override) {
                        return [
                            'field_name' => $override->field_name,
                            'field_type' => $override->field_type,
                            'source_field' => $override->source_field,
                            'transform_rules' => $override->transform_rules,
                        ];
                    }),
                    'created_at' => $mapping->created_at->toISOString(),
                    'updated_at' => $mapping->updated_at->toISOString(),
                ];
            }),
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->json($data, 200, $headers);
    }

    /**
     * Show migration activity and job tracking
     */
    public function migrationActivity()
    {
        // Get migration job statistics
        $jobStats = [
            'total_jobs' => 0,
            'running_jobs' => 0,
            'completed_jobs' => 0,
            'failed_jobs' => 0,
        ];

        $recentJobs = [];
        $activeJobs = [];

        // Check if migration progress service is available
        if (class_exists(\Westlinks\Wlcms\Services\MigrationProgressService::class)) {
            try {
                // Check if the migration jobs table exists before using the service
                if (\Illuminate\Support\Facades\Schema::hasTable('cms_legacy_migration_jobs')) {
                    $progressService = app(\Westlinks\Wlcms\Services\MigrationProgressService::class);
                    $jobStats = $progressService->getMigrationSummary();
                    $recentJobs = $progressService->getJobHistory(15);
                    $activeJobs = $progressService->getActiveJobs();
                } else {
                    logger('Migration jobs table not found - run migrations to enable job tracking');
                }
            } catch (\Exception $e) {
                // Fallback if progress service isn't available
                logger('Migration progress service error: ' . $e->getMessage());
            }
        }

        // Get recent article mappings activity
        $recentMappings = CmsLegacyArticleMapping::with(['contentItem'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($mapping) {
                return [
                    'id' => $mapping->id,
                    'legacy_article_id' => $mapping->legacy_article_id,
                    'content_title' => $mapping->contentItem->title ?? 'Unknown',
                    'status' => $mapping->is_active ? 'active' : 'inactive',
                    'last_sync' => $mapping->last_sync_at?->diffForHumans(),
                    'created_at' => $mapping->created_at->diffForHumans(),
                ];
            });

        // Get legacy database connection status
        $connectionStatus = 'unknown';
        try {
            if (class_exists(\Westlinks\Wlcms\Services\LegacyDatabaseService::class)) {
                $legacyDb = app(\Westlinks\Wlcms\Services\LegacyDatabaseService::class);
                $testResult = $legacyDb->testConnection();
                $connectionStatus = $testResult['status'];
            }
        } catch (\Exception $e) {
            $connectionStatus = 'error';
        }

        return view('wlcms::admin.legacy.migration.activity', compact(
            'jobStats',
            'recentJobs', 
            'activeJobs',
            'recentMappings',
            'connectionStatus'
        ));
    }
}