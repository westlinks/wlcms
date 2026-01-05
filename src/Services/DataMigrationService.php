<?php

namespace Westlinks\Wlcms\Services;

use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\CmsLegacyArticleMapping;
use Westlinks\Wlcms\Models\CmsLegacyFieldOverride;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DataMigrationService
{
    protected LegacyDatabaseService $legacyDb;
    protected FieldTransformationService $transformer;
    protected array $migrationStats = [];

    public function __construct(
        LegacyDatabaseService $legacyDb,
        FieldTransformationService $transformer
    ) {
        $this->legacyDb = $legacyDb;
        $this->transformer = $transformer;
        $this->resetStats();
    }

    /**
     * Migrate a batch of legacy articles to CMS
     */
    public function migrateBatch(array $options = []): array
    {
        $this->resetStats();
        
        $batchSize = $options['batch_size'] ?? 25;
        $contentType = $options['content_type'] ?? 'article';
        $preserveHierarchy = $options['preserve_hierarchy'] ?? true;
        $createRedirects = $options['create_redirects'] ?? true;
        
        try {
            // Get unmapped articles
            $legacyArticles = $this->legacyDb->getUnmappedArticles($batchSize);
            
            if ($legacyArticles->isEmpty()) {
                return [
                    'status' => 'completed',
                    'message' => 'No unmapped articles found',
                    'stats' => $this->migrationStats,
                ];
            }
            
            foreach ($legacyArticles as $legacyArticle) {
                try {
                    $this->migrateArticle($legacyArticle, $contentType, $options);
                    $this->migrationStats['success']++;
                } catch (\Exception $e) {
                    $this->migrationStats['errors']++;
                    $this->migrationStats['error_details'][] = [
                        'article_id' => $legacyArticle->id,
                        'error' => $e->getMessage(),
                    ];
                    \Log::error('Article migration failed', [
                        'article_id' => $legacyArticle->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                $this->migrationStats['total']++;
            }
            
            return [
                'status' => 'completed',
                'message' => $this->generateSummaryMessage(),
                'stats' => $this->migrationStats,
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Batch migration failed: ' . $e->getMessage(),
                'stats' => $this->migrationStats,
            ];
        }
    }

    /**
     * Migrate a single legacy article to CMS
     */
    public function migrateArticle(object $legacyArticle, string $contentType = 'article', array $options = []): CmsLegacyArticleMapping
    {
        // Transform legacy data to CMS format
        $cmsData = $this->transformArticleData($legacyArticle, $contentType, $options);
        
        // Create CMS content item
        $contentItem = ContentItem::create($cmsData);
        
        // Create mapping record
        $mapping = CmsLegacyArticleMapping::create([
            'cms_content_item_id' => $contentItem->id,
            'legacy_article_id' => $legacyArticle->id,
            'mapping_type' => $options['mapping_type'] ?? 'migration',
            'is_active' => true,
            'sync_frequency' => $options['sync_frequency'] ?? 'manual',
            'field_mappings' => $this->generateFieldMappings($legacyArticle),
            'last_sync_at' => now(),
            'metadata' => [
                'migration_date' => now()->toISOString(),
                'source_table' => config('wlcms.legacy.article_table', 'articles'),
                'migration_options' => $options,
            ],
        ]);
        
        // Create field overrides if specified
        if (!empty($options['field_overrides'])) {
            $this->createFieldOverrides($mapping->id, $options['field_overrides']);
        }
        
        // Create redirects if requested
        if ($options['create_redirects'] ?? false) {
            $this->createRedirect($legacyArticle, $contentItem);
        }
        
        return $mapping;
    }

    /**
     * Transform legacy article data to CMS format
     */
    protected function transformArticleData(object $legacyArticle, string $contentType, array $options = []): array
    {
        $fieldMappings = config('wlcms.legacy.field_mappings', []);
        $transformedData = [];
        
        // Basic field mapping
        $transformedData['type'] = $contentType;
        $transformedData['title'] = $this->transformer->cleanText($legacyArticle->title ?? 'Untitled');
        $transformedData['slug'] = $this->generateUniqueSlug($legacyArticle);
        $transformedData['content'] = $this->transformer->transformContent($legacyArticle->content ?? '');
        $transformedData['summary'] = $this->transformer->cleanText($legacyArticle->excerpt ?? $legacyArticle->summary ?? '');
        
        // Status mapping
        $transformedData['status'] = $this->mapStatus($legacyArticle->status ?? 'published');
        
        // Dates
        $transformedData['published_at'] = $this->parseDate($legacyArticle->published_at ?? $legacyArticle->created_at ?? now());
        $transformedData['created_at'] = $this->parseDate($legacyArticle->created_at ?? now());
        $transformedData['updated_at'] = $this->parseDate($legacyArticle->updated_at ?? now());
        
        // SEO fields
        $transformedData['meta_title'] = $this->transformer->cleanText($legacyArticle->meta_title ?? $transformedData['title']);
        $transformedData['meta_description'] = $this->transformer->cleanText($legacyArticle->meta_description ?? $transformedData['summary']);
        $transformedData['meta_keywords'] = $this->transformer->cleanText($legacyArticle->meta_keywords ?? '');
        
        // Custom field mappings
        foreach ($fieldMappings as $cmsField => $legacyField) {
            if (isset($legacyArticle->{$legacyField}) && !isset($transformedData[$cmsField])) {
                $transformedData[$cmsField] = $this->transformer->transformField(
                    $legacyArticle->{$legacyField},
                    $cmsField,
                    $legacyField
                );
            }
        }
        
        // Additional metadata
        $transformedData['metadata'] = [
            'legacy_id' => $legacyArticle->id,
            'migration_source' => 'legacy_import',
            'original_data' => json_encode($legacyArticle),
        ];
        
        return array_filter($transformedData, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Generate unique slug for article
     */
    protected function generateUniqueSlug(object $legacyArticle): string
    {
        $baseSlug = '';
        
        // Try to use existing slug first
        if (!empty($legacyArticle->slug)) {
            $baseSlug = Str::slug($legacyArticle->slug);
        } elseif (!empty($legacyArticle->title)) {
            $baseSlug = Str::slug($legacyArticle->title);
        } else {
            $baseSlug = 'article-' . $legacyArticle->id;
        }
        
        // Ensure uniqueness
        $slug = $baseSlug;
        $counter = 1;
        
        while (ContentItem::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Map legacy status to CMS status
     */
    protected function mapStatus(string $legacyStatus): string
    {
        $statusMap = config('wlcms.legacy.status_mapping', [
            'published' => 'published',
            'draft' => 'draft',
            'pending' => 'pending',
            'private' => 'private',
            'archived' => 'archived',
            'active' => 'published',
            'inactive' => 'draft',
            '1' => 'published',
            '0' => 'draft',
        ]);
        
        return $statusMap[strtolower($legacyStatus)] ?? 'draft';
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate($dateValue): Carbon
    {
        if (is_null($dateValue)) {
            return now();
        }
        
        try {
            if (is_numeric($dateValue)) {
                // Unix timestamp
                return Carbon::createFromTimestamp($dateValue);
            }
            
            return Carbon::parse($dateValue);
        } catch (\Exception $e) {
            return now();
        }
    }

    /**
     * Generate field mappings for the article
     */
    protected function generateFieldMappings(object $legacyArticle): array
    {
        $mappings = [];
        $configMappings = config('wlcms.legacy.field_mappings', []);
        
        foreach ($configMappings as $cmsField => $legacyField) {
            if (property_exists($legacyArticle, $legacyField)) {
                $mappings[$cmsField] = [
                    'source_field' => $legacyField,
                    'transformation' => 'direct',
                    'value_type' => $this->detectValueType($legacyArticle->{$legacyField}),
                ];
            }
        }
        
        return $mappings;
    }

    /**
     * Detect the type of a value
     */
    protected function detectValueType($value): string
    {
        if (is_null($value)) return 'null';
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'float';
        if (is_array($value) || is_object($value)) return 'json';
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) return 'date';
        if (strlen($value) > 255) return 'text';
        return 'string';
    }

    /**
     * Create field overrides for specific mappings
     */
    protected function createFieldOverrides(int $mappingId, array $overrides): void
    {
        foreach ($overrides as $override) {
            CmsLegacyFieldOverride::create([
                'cms_legacy_article_mapping_id' => $mappingId,
                'field_name' => $override['field_name'],
                'field_type' => $override['field_type'],
                'source_field' => $override['source_field'],
                'transform_rules' => $override['transform_rules'] ?? [],
                'is_active' => $override['is_active'] ?? true,
            ]);
        }
    }

    /**
     * Create redirect from legacy URL to new CMS URL
     */
    protected function createRedirect(object $legacyArticle, ContentItem $contentItem): void
    {
        // This would integrate with a redirect management system
        // For now, just log the redirect that should be created
        \Log::info('Redirect needed', [
            'from' => $this->generateLegacyUrl($legacyArticle),
            'to' => route('wlcms.frontend.show', $contentItem->slug),
            'status_code' => 301,
        ]);
    }

    /**
     * Generate legacy URL pattern
     */
    protected function generateLegacyUrl(object $legacyArticle): string
    {
        $urlPattern = config('wlcms.legacy.url_pattern', '/article/{id}');
        
        return str_replace([
            '{id}',
            '{slug}',
            '{title}',
        ], [
            $legacyArticle->id,
            $legacyArticle->slug ?? '',
            Str::slug($legacyArticle->title ?? ''),
        ], $urlPattern);
    }

    /**
     * Sync existing mapping with latest legacy data
     */
    public function syncMapping(CmsLegacyArticleMapping $mapping): array
    {
        try {
            $legacyArticle = $this->legacyDb->getArticle($mapping->legacy_article_id);
            
            if (!$legacyArticle) {
                throw new \Exception('Legacy article not found');
            }
            
            // Transform updated data
            $cmsData = $this->transformArticleData($legacyArticle, $mapping->contentItem->type ?? 'article');
            
            // Update CMS content item
            $mapping->contentItem->update($cmsData);
            
            // Update mapping sync status
            $mapping->update([
                'last_sync_at' => now(),
                'sync_error' => null,
            ]);
            
            return [
                'status' => 'success',
                'message' => 'Mapping synced successfully',
                'mapping_id' => $mapping->id,
            ];
            
        } catch (\Exception $e) {
            // Update mapping with error
            $mapping->update([
                'last_sync_at' => now(),
                'sync_error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Sync failed: ' . $e->getMessage(),
                'mapping_id' => $mapping->id,
            ];
        }
    }

    /**
     * Reset migration statistics
     */
    protected function resetStats(): void
    {
        $this->migrationStats = [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'error_details' => [],
            'started_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate summary message from stats
     */
    protected function generateSummaryMessage(): string
    {
        $stats = $this->migrationStats;
        
        $message = "Migration completed: {$stats['success']} articles migrated successfully";
        
        if ($stats['errors'] > 0) {
            $message .= ", {$stats['errors']} errors occurred";
        }
        
        return $message;
    }

    /**
     * Get current migration statistics
     */
    public function getStats(): array
    {
        return $this->migrationStats;
    }
}