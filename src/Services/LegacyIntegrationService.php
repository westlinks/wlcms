<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\CmsLegacyArticleMapping;
use Westlinks\Wlcms\Models\CmsLegacyFieldOverride;
use Westlinks\Wlcms\Models\CmsLegacyNavigationItem;

class LegacyIntegrationService
{
    /**
     * Check if legacy integration is enabled
     */
    public function isEnabled(): bool
    {
        return config('wlcms.legacy.enabled', false);
    }

    /**
     * Get the configured legacy article model class
     */
    public function getArticleModel(): string
    {
        return config('wlcms.legacy.article_model', 'App\Models\Article');
    }

    /**
     * Get the configured legacy article table name
     */
    public function getArticleTable(): string
    {
        return config('wlcms.legacy.article_table', 'articles');
    }

    /**
     * Get field mappings configuration
     */
    public function getFieldMappings(): array
    {
        return config('wlcms.legacy.field_mappings', []);
    }

    /**
     * Create a new legacy article mapping
     */
    public function createMapping(int $cmsId, int $articleId, array $options = []): CmsLegacyArticleMapping
    {
        if (!$this->isEnabled()) {
            throw new \Exception('Legacy integration is not enabled');
        }

        $mappingData = array_merge([
            'cms_content_item_id' => $cmsId,
            'legacy_article_id' => $articleId,
            'mapping_type' => 'replacement',
            'sort_order' => 0,
            'is_active' => true,
            'metadata' => [],
        ], $options);

        return CmsLegacyArticleMapping::create($mappingData);
    }

    /**
     * Migrate a legacy article to CMS content
     */
    public function migrateArticleToCms(Model $article, array $options = []): ContentItem
    {
        if (!$this->isEnabled()) {
            throw new \Exception('Legacy integration is not enabled');
        }

        $fieldMappings = $this->getFieldMappings();
        $cmsData = [];

        // Map legacy fields to CMS fields
        foreach ($fieldMappings as $cmsField => $legacyField) {
            if (isset($article->$legacyField)) {
                $cmsData[$cmsField] = $article->$legacyField;
            }
        }

        // Set default values for required CMS fields
        $cmsData = array_merge([
            'title' => $article->title ?? 'Untitled',
            'content' => $article->description ?? $article->content ?? '',
            'type' => 'page',
            'status' => $article->published ? 'published' : 'draft',
            'published_at' => $article->published ? now() : null,
        ], $cmsData, $options);

        // Create the CMS content item
        $contentItem = ContentItem::create($cmsData);

        // Create the mapping
        $mapping = $this->createMapping(
            $contentItem->id, 
            $article->id,
            ['mapping_type' => 'migration']
        );

        Log::info('Legacy article migrated to CMS', [
            'legacy_id' => $article->id,
            'cms_id' => $contentItem->id,
            'mapping_id' => $mapping->id
        ]);

        return $contentItem;
    }

    /**
     * Get effective article data with field overrides applied
     */
    public function getEffectiveArticleData(Model $article): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $mapping = CmsLegacyArticleMapping::where('legacy_article_id', $article->id)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return $article->toArray();
        }

        return $mapping->getEffectiveArticleData();
    }

    /**
     * Sync field overrides for a mapping
     */
    public function syncFieldOverrides(CmsLegacyArticleMapping $mapping, array $fields): void
    {
        if (!$this->isEnabled()) {
            throw new \Exception('Legacy integration is not enabled');
        }

        DB::transaction(function () use ($mapping, $fields) {
            // Get existing overrides
            $existingOverrides = $mapping->fieldOverrides()
                ->get()
                ->keyBy('field_name');

            foreach ($fields as $fieldName => $value) {
                if ($existingOverrides->has($fieldName)) {
                    // Update existing override
                    $override = $existingOverrides->get($fieldName);
                    $override->setTypedValue($value);
                    $override->is_active = true;
                    $override->save();
                    $existingOverrides->forget($fieldName);
                } else {
                    // Create new override
                    $override = new CmsLegacyFieldOverride([
                        'cms_legacy_article_mapping_id' => $mapping->id,
                        'field_name' => $fieldName,
                        'is_active' => true,
                    ]);
                    $override->setTypedValue($value);
                    $override->save();
                }
            }

            // Deactivate remaining overrides not in the new fields
            foreach ($existingOverrides as $override) {
                $override->update(['is_active' => false]);
            }
        });
    }

    /**
     * Create navigation items for a content item
     */
    public function createNavigationItems(ContentItem $content, array $contexts = ['main']): void
    {
        if (!$this->isEnabled()) {
            throw new \Exception('Legacy integration is not enabled');
        }

        foreach ($contexts as $context) {
            CmsLegacyNavigationItem::create([
                'cms_content_item_id' => $content->id,
                'navigation_context' => $context,
                'label' => $content->title,
                'slug' => $content->slug,
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 0,
            ]);
        }

        Log::info('Navigation items created for content', [
            'content_id' => $content->id,
            'contexts' => $contexts
        ]);
    }

    /**
     * Get navigation tree for a given context
     */
    public function getNavigationTree(string $context = 'main'): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $items = CmsLegacyNavigationItem::with('contentItem')
            ->inContext($context)
            ->active()
            ->inMenu()
            ->rootItems()
            ->ordered()
            ->get();

        return $this->buildNavigationTree($items);
    }

    /**
     * Build hierarchical navigation tree
     */
    protected function buildNavigationTree($items): array
    {
        $tree = [];

        foreach ($items as $item) {
            $node = [
                'id' => $item->id,
                'label' => $item->getEffectiveLabel(),
                'slug' => $item->getEffectiveSlug(),
                'url' => $item->contentItem->url ?? '#',
                'css_class' => $item->css_class,
                'icon' => $item->icon,
                'target' => $item->target,
                'children' => $this->buildNavigationTree($item->activeChildren)
            ];

            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Validate legacy article model exists and is accessible
     */
    public function validateArticleModel(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $modelClass = $this->getArticleModel();
        
        if (!class_exists($modelClass)) {
            return false;
        }

        try {
            // Try to instantiate the model
            $model = new $modelClass;
            return $model instanceof Model;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get migration configuration
     */
    public function getMigrationConfig(): array
    {
        return config('wlcms.legacy.migration', [
            'enabled' => true,
            'batch_size' => 50,
            'preserve_urls' => true,
            'create_redirects' => true,
        ]);
    }

    /**
     * Check if migrations are enabled
     */
    public function isMigrationEnabled(): bool
    {
        return $this->isEnabled() && 
               config('wlcms.legacy.migration.enabled', true);
    }

    /**
     * Synchronize a legacy article with its CMS content
     */
    public function syncArticle(CmsLegacyArticleMapping $mapping): array
    {
        try {
            // Get the legacy article data
            $articleModel = $this->getArticleModel();
            $article = $articleModel::find($mapping->legacy_article_id);
            
            if (!$article) {
                throw new \Exception("Legacy article #{$mapping->legacy_article_id} not found");
            }

            // Get the CMS content item
            $contentItem = $mapping->contentItem;
            if (!$contentItem) {
                throw new \Exception("CMS content item not found for mapping #{$mapping->id}");
            }

            // Get effective article data with field mappings applied
            $articleData = $this->getEffectiveArticleData($article);
            
            // Apply field overrides if any exist
            foreach ($mapping->fieldOverrides as $override) {
                if ($override->is_active && !empty($override->override_value)) {
                    $articleData[$override->field_name] = $override->override_value;
                }
            }

            // Update the CMS content item based on sync direction
            $syncDirection = $mapping->sync_direction ?? 'legacy_to_cms';
            
            if ($syncDirection === 'legacy_to_cms' || $syncDirection === 'bidirectional') {
                // Update CMS content from legacy data
                $updateData = [];
                
                // Map common fields
                if (isset($articleData['title'])) {
                    $updateData['title'] = $articleData['title'];
                }
                if (isset($articleData['content'])) {
                    $updateData['content'] = $articleData['content'];
                }
                if (isset($articleData['excerpt'])) {
                    $updateData['excerpt'] = $articleData['excerpt'];
                }
                if (isset($articleData['slug'])) {
                    $updateData['slug'] = $articleData['slug'];
                }
                if (isset($articleData['published_at'])) {
                    $updateData['published_at'] = $articleData['published_at'];
                }
                if (isset($articleData['status'])) {
                    $updateData['status'] = $articleData['status'];
                }

                // Update meta data with additional fields
                $meta = $contentItem->meta ?? [];
                foreach ($articleData as $key => $value) {
                    if (!in_array($key, ['title', 'content', 'excerpt', 'slug', 'published_at', 'status'])) {
                        $meta['legacy_' . $key] = $value;
                    }
                }
                $updateData['meta'] = $meta;

                $contentItem->update($updateData);
            }

            // Update mapping sync status
            $mapping->update([
                'last_sync_at' => now(),
                'sync_error' => null,
            ]);

            return [
                'status' => 'success',
                'message' => 'Article synchronized successfully',
                'updated_fields' => array_keys($updateData ?? []),
                'sync_direction' => $syncDirection,
                'synced_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            // Update mapping with error
            $mapping->update([
                'last_sync_at' => now(),
                'sync_error' => $e->getMessage(),
            ]);

            Log::error('Article sync failed', [
                'mapping_id' => $mapping->id,
                'legacy_article_id' => $mapping->legacy_article_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_at' => now()->toISOString(),
            ];
        }
    }
}