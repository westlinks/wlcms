# WLCMS Legacy Article Integration Specification

## Overview

This specification outlines the enhancement of WLCMS to support seamless integration with legacy article systems commonly found in SaaS products. The feature will be optional, configurable, and designed to maintain WLCMS's clean architecture for general public use while providing powerful legacy integration capabilities for specific use cases.

## Business Context

Multiple SaaS products share a common legacy article structure with 24+ fields. Rather than building external bridge systems, WLCMS will be enhanced to natively support legacy article integration, making it a more powerful and reusable solution across the SaaS product portfolio.

## Goals

1. **Optional Feature**: Disabled by default, enabled through configuration
2. **Clean Integration**: Native WLCMS UI support for legacy article management
3. **Field-Level Control**: Map and override any legacy article field individually  
4. **Migration Support**: Tools to migrate legacy articles to CMS content
5. **Backward Compatibility**: Zero impact on pure CMS usage
6. **SaaS Reusability**: Configurable for different legacy article schemas

## Architecture Overview

### Configuration-Driven Approach

```php
// config/wlcms.php - New legacy section
'legacy' => [
    'enabled' => env('WLCMS_LEGACY_INTEGRATION', false),
    'article_table' => env('WLCMS_LEGACY_TABLE', 'articles'),
    'article_model' => env('WLCMS_LEGACY_MODEL', 'App\Models\Article'),
    'route_prefix' => env('WLCMS_LEGACY_ROUTE_PREFIX', 'articles'),
    
    'field_mappings' => [
        // Content fields
        'title' => 'title',
        'subtitle' => 'subtitle', 
        'content' => 'description',
        'intro' => 'intro',
        'abstract' => 'abstract',
        'slug' => 'slug',
        'menu_title' => 'menu_title',
        
        // Image fields
        'image' => 'image',
        'image_caption' => 'image_caption',
        'image_width' => 'image_width',
        'image_height' => 'image_height',
        'image_position' => 'image_position', 
        'image_credits' => 'image_credits',
        
        // Display settings
        'is_rich_text' => 'is_rich_text',
        'sort' => 'sort',
        'published' => 'published',
        'top_menu' => 'top_menu',
        
        // Hierarchy
        'is_parent' => 'is_parent',
        'parent_id' => 'parent_id',
        
        // Management
        'template_id' => 'template_id',
        'created_by' => 'created_by',
        'updated_by' => 'updated_by',
    ],
    
    'migration' => [
        'enabled' => env('WLCMS_LEGACY_MIGRATION', true),
        'batch_size' => 50,
        'preserve_urls' => true,
        'create_redirects' => true,
    ]
]
```

## Database Structure

### Enhanced WLCMS Tables

#### 1. Legacy Article Mapping (`cms_legacy_article_mappings`)

```sql
CREATE TABLE cms_legacy_article_mappings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cms_content_item_id BIGINT UNSIGNED NOT NULL,
    legacy_article_id BIGINT UNSIGNED NOT NULL,
    mapping_type ENUM('replacement', 'supplement', 'redirect', 'migration') DEFAULT 'replacement',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_cms_content (cms_content_item_id),
    INDEX idx_legacy_article (legacy_article_id),
    INDEX idx_active_type (is_active, mapping_type),
    UNIQUE KEY unique_mapping (cms_content_item_id, legacy_article_id),
    
    FOREIGN KEY (cms_content_item_id) REFERENCES cms_content_items(id) ON DELETE CASCADE
);
```

#### 2. Legacy Field Overrides (`cms_legacy_field_overrides`)

```sql
CREATE TABLE cms_legacy_field_overrides (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cms_legacy_article_mapping_id BIGINT UNSIGNED NOT NULL,
    field_name VARCHAR(50) NOT NULL,
    override_value TEXT NULL,
    field_type ENUM('string', 'text', 'integer', 'boolean', 'json', 'datetime') DEFAULT 'string',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_mapping_field (cms_legacy_article_mapping_id, field_name),
    INDEX idx_active (is_active),
    UNIQUE KEY unique_field_override (cms_legacy_article_mapping_id, field_name),
    
    FOREIGN KEY (cms_legacy_article_mapping_id) REFERENCES cms_legacy_article_mappings(id) ON DELETE CASCADE
);
```

#### 3. Legacy Navigation Items (`cms_legacy_navigation_items`)

```sql
CREATE TABLE cms_legacy_navigation_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cms_content_item_id BIGINT UNSIGNED NOT NULL,
    navigation_context ENUM('main', 'footer', 'sidebar', 'breadcrumb') DEFAULT 'main',
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    label VARCHAR(255) NULL,
    slug VARCHAR(255) NULL,
    css_class VARCHAR(255) NULL,
    icon VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    show_in_menu BOOLEAN DEFAULT TRUE,
    target ENUM('_self', '_blank', '_parent', '_top') DEFAULT '_self',
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_cms_content (cms_content_item_id),
    INDEX idx_parent (parent_id),
    INDEX idx_navigation_context (navigation_context, is_active),
    INDEX idx_slug (slug),
    
    FOREIGN KEY (parent_id) REFERENCES cms_legacy_navigation_items(id) ON DELETE CASCADE,
    FOREIGN KEY (cms_content_item_id) REFERENCES cms_content_items(id) ON DELETE CASCADE
);
```

## Models and Services

### 1. Enhanced CmsContentItem Model

```php
// Add to existing CmsContentItem model
public function legacyArticleMappings()
{
    return $this->hasMany(CmsLegacyArticleMapping::class);
}

public function activeLegacyMappings()
{
    return $this->legacyArticleMappings()->where('is_active', true);
}

public function getLegacyArticle()
{
    $mapping = $this->activeLegacyMappings()->first();
    if ($mapping && class_exists(config('wlcms.legacy.article_model'))) {
        $model = config('wlcms.legacy.article_model');
        return $model::find($mapping->legacy_article_id);
    }
    return null;
}
```

### 2. New Models

#### CmsLegacyArticleMapping
- Handle article-to-CMS mapping
- Field override management
- Effective data calculation

#### CmsLegacyFieldOverride  
- Individual field overrides
- Type casting and validation
- Active/inactive management

#### CmsLegacyNavigationItem
- Hierarchical navigation management
- Context-based organization
- Integration with legacy routing

### 3. LegacyIntegrationService

```php
class LegacyIntegrationService
{
    public function isEnabled(): bool
    public function getArticleModel(): string
    public function createMapping(int $cmsId, int $articleId, array $options): CmsLegacyArticleMapping
    public function migrateArticleToCms(Model $article, array $options): CmsContentItem
    public function getEffectiveArticleData(Model $article): array
    public function syncFieldOverrides(CmsLegacyArticleMapping $mapping, array $fields): void
    public function createNavigationItems(CmsContentItem $content, array $contexts): void
}
```

## UI Enhancements

### 1. Content Item Creation/Edit Forms

**Legacy Article Integration Panel** (when enabled):
- Article selector dropdown
- Mapping type selection (replacement/supplement/redirect/migration)
- Field override interface with tabs:
  - Content Fields (title, subtitle, intro, etc.)
  - Image Fields (image, caption, dimensions, etc.)  
  - Display Settings (rich text, published, menu flags)
  - Hierarchy Settings (parent relationships)

### 2. Navigation Management

**Legacy Navigation Builder**:
- Drag-and-drop navigation tree
- Context switching (main/footer/sidebar/breadcrumb)
- Per-item configuration (label, slug, CSS class, icon)
- Hierarchical relationships

### 3. Migration Tools

**Legacy Article Migration Interface**:
- Bulk article selection
- Field mapping configuration
- Preview before migration
- Batch processing with progress tracking
- Rollback capability

**Migration Dashboard**:
- Migration status overview
- Article mapping statistics  
- URL redirect management
- Conflict resolution tools

### 4. Legacy Content Browser

**Article Browser Component**:
- Searchable article list
- Filter by publication status, parent/child, template
- Preview article content
- Quick mapping creation
- Batch operations

## Route Integration

### 1. Conditional Route Registration

```php
// In WlcmsServiceProvider
if (config('wlcms.legacy.enabled')) {
    $this->loadRoutesFrom(__DIR__.'/routes/legacy.php');
}
```

### 2. Legacy Route Handling

```php
// routes/legacy.php
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix(config('wlcms.layout.embedded_prefix', 'cms'))->group(function () {
        Route::resource('legacy-mappings', LegacyMappingController::class);
        Route::resource('legacy-navigation', LegacyNavigationController::class);
        Route::post('legacy-migration/preview', [LegacyMigrationController::class, 'preview']);
        Route::post('legacy-migration/execute', [LegacyMigrationController::class, 'execute']);
    });
});
```

## Commands

### 1. Legacy Migration Command

```bash
php artisan wlcms:migrate-articles
    --model=App\\Models\\Article
    --batch=50
    --preview
    --force
```

### 2. Field Mapping Sync Command

```bash
php artisan wlcms:sync-field-mappings
    --mapping-id=123
    --dry-run
```

### 3. Navigation Rebuild Command

```bash
php artisan wlcms:rebuild-navigation
    --context=main
    --force
```

## Configuration Management

### 1. Legacy Feature Detection

```php
// Blade directive
@legacyEnabled
    <!-- Legacy-specific UI elements -->
@endlegacyEnabled

// Service helper
if (app(LegacyIntegrationService::class)->isEnabled()) {
    // Legacy functionality
}
```

### 2. Environment Configuration

```env
# Basic enablement
WLCMS_LEGACY_INTEGRATION=true
WLCMS_LEGACY_TABLE=articles
WLCMS_LEGACY_MODEL="App\Models\Article"

# Advanced configuration
WLCMS_LEGACY_ROUTE_PREFIX=articles
WLCMS_LEGACY_MIGRATION=true
```

## Implementation Phases

### Phase 1: Database and Models
- Create migration files for legacy tables
- Implement core model relationships
- Basic service layer for legacy integration
- Configuration system setup

### Phase 2: Core Functionality  
- Legacy article mapping creation
- Field override system
- Basic CRUD operations for mappings
- Command structure

### Phase 3: UI Integration
- Content item creation with legacy support
- Field override interface
- Basic navigation management
- Legacy content browser

### Phase 4: Migration Tools
- Article-to-CMS migration functionality
- Bulk processing capabilities
- Preview and rollback features
- Migration dashboard

### Phase 5: Advanced Features
- Navigation builder interface
- URL redirect management
- Advanced field mapping options
- Performance optimizations

## Backward Compatibility

### 1. Feature Flags
- All legacy functionality behind configuration flags
- Graceful degradation when disabled
- No impact on existing WLCMS installations

### 2. Database Isolation
- Legacy tables separate from core WLCMS tables
- Optional migration installation
- Clean uninstall capability

### 3. UI Isolation
- Legacy UI components only load when enabled
- No JavaScript/CSS overhead for pure CMS usage
- Conditional view compilation

## Testing Strategy

### 1. Feature Toggle Testing
- Test with legacy features enabled/disabled
- Ensure clean operation in both modes
- Configuration validation

### 2. Legacy Integration Testing  
- Article mapping functionality
- Field override behavior
- Navigation integration
- Migration processes

### 3. Performance Testing
- Large article dataset handling
- Bulk migration performance
- UI responsiveness with legacy data

## Success Criteria

1. **Clean Integration**: Legacy features seamlessly integrated into WLCMS UI
2. **Zero Impact**: No performance or functionality impact when disabled
3. **Complete Coverage**: All 24 article fields supported with overrides
4. **Migration Success**: Reliable article-to-CMS migration with rollback
5. **SaaS Ready**: Configurable for different SaaS product needs
6. **Documentation**: Complete user and developer documentation

## Next Steps

1. Review and approve specification
2. Create detailed implementation plan
3. Set up development environment in wlcms_codebase
4. Begin Phase 1 implementation
5. Establish testing protocols
6. Plan integration with existing SaaS products

---

This specification provides the foundation for a comprehensive legacy article integration feature that maintains WLCMS's clean architecture while providing powerful integration capabilities for SaaS products with legacy article systems.