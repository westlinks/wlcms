<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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
}