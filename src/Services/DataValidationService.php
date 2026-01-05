<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class DataValidationService
{
    protected LegacyDatabaseService $legacyDb;
    protected array $validationRules = [];
    protected array $validationResults = [];

    public function __construct(LegacyDatabaseService $legacyDb)
    {
        $this->legacyDb = $legacyDb;
        $this->initializeDefaultRules();
    }

    /**
     * Validate legacy database structure and data integrity
     */
    public function validateLegacyDatabase(): array
    {
        $results = [
            'status' => 'success',
            'tests' => [],
            'warnings' => [],
            'errors' => [],
            'recommendations' => [],
        ];

        // Test database connection
        $connectionTest = $this->legacyDb->testConnection();
        $results['tests']['connection'] = $connectionTest;
        
        if ($connectionTest['status'] !== 'success') {
            $results['status'] = 'error';
            $results['errors'][] = 'Database connection failed: ' . $connectionTest['message'];
            return $results;
        }

        // Validate table structure
        $structureValidation = $this->validateTableStructure();
        $results['tests']['structure'] = $structureValidation;
        
        if (!empty($structureValidation['errors'])) {
            $results['errors'] = array_merge($results['errors'], $structureValidation['errors']);
            $results['status'] = 'warning';
        }
        
        if (!empty($structureValidation['warnings'])) {
            $results['warnings'] = array_merge($results['warnings'], $structureValidation['warnings']);
        }

        // Validate data integrity
        $integrityValidation = $this->validateDataIntegrity();
        $results['tests']['integrity'] = $integrityValidation;
        
        if (!empty($integrityValidation['errors'])) {
            $results['errors'] = array_merge($results['errors'], $integrityValidation['errors']);
            $results['status'] = 'error';
        }
        
        if (!empty($integrityValidation['warnings'])) {
            $results['warnings'] = array_merge($results['warnings'], $integrityValidation['warnings']);
        }

        // Performance and optimization checks
        $performanceValidation = $this->validatePerformance();
        $results['tests']['performance'] = $performanceValidation;
        
        if (!empty($performanceValidation['warnings'])) {
            $results['warnings'] = array_merge($results['warnings'], $performanceValidation['warnings']);
        }
        
        $results['recommendations'] = array_merge($results['recommendations'], $performanceValidation['recommendations']);

        return $results;
    }

    /**
     * Validate legacy table structure
     */
    protected function validateTableStructure(): array
    {
        $results = ['errors' => [], 'warnings' => [], 'info' => []];
        
        // Check required tables exist
        $requiredTables = config('wlcms.legacy.required_tables', ['articles']);
        $existingTables = $this->legacyDb->getTableList();
        
        foreach ($requiredTables as $table) {
            if (!in_array($table, $existingTables)) {
                $results['errors'][] = "Required table '{$table}' not found";
            } else {
                $results['info'][] = "Table '{$table}' found";
                
                // Validate table structure
                $structure = $this->legacyDb->getTableStructure($table);
                $this->validateTableColumns($table, $structure, $results);
            }
        }
        
        return $results;
    }

    /**
     * Validate columns in a specific table
     */
    protected function validateTableColumns(string $tableName, array $structure, array &$results): void
    {
        $requiredColumns = config("wlcms.legacy.table_schemas.{$tableName}", []);
        
        if (empty($requiredColumns)) {
            return;
        }
        
        $existingColumns = array_column($structure, 'name');
        
        foreach ($requiredColumns as $column => $rules) {
            if (!in_array($column, $existingColumns)) {
                if ($rules['required'] ?? false) {
                    $results['errors'][] = "Required column '{$column}' missing in table '{$tableName}'";
                } else {
                    $results['warnings'][] = "Optional column '{$column}' missing in table '{$tableName}'";
                }
            } else {
                // Validate column type
                $columnInfo = collect($structure)->firstWhere('name', $column);
                if ($columnInfo && isset($rules['type'])) {
                    $this->validateColumnType($tableName, $column, $columnInfo, $rules, $results);
                }
            }
        }
    }

    /**
     * Validate column data type
     */
    protected function validateColumnType(string $table, string $column, array $columnInfo, array $rules, array &$results): void
    {
        $actualType = strtolower($columnInfo['type']);
        $expectedTypes = (array) $rules['type'];
        
        $typeMatch = false;
        foreach ($expectedTypes as $expectedType) {
            if (str_contains($actualType, strtolower($expectedType))) {
                $typeMatch = true;
                break;
            }
        }
        
        if (!$typeMatch) {
            $results['warnings'][] = "Column '{$column}' in table '{$table}' has type '{$actualType}', expected one of: " . implode(', ', $expectedTypes);
        }
    }

    /**
     * Validate data integrity
     */
    protected function validateDataIntegrity(): array
    {
        $results = ['errors' => [], 'warnings' => [], 'info' => []];
        
        // Check article data integrity
        $this->validateArticleData($results);
        
        // Check for orphaned records
        $this->checkOrphanedRecords($results);
        
        // Check for duplicate data
        $this->checkDuplicateData($results);
        
        // Check data consistency
        $this->checkDataConsistency($results);
        
        return $results;
    }

    /**
     * Validate article data specifically
     */
    protected function validateArticleData(array &$results): void
    {
        try {
            $articleTable = config('wlcms.legacy.article_table', 'articles');
            $totalArticles = $this->legacyDb->getArticleCount();
            
            $results['info'][] = "Total articles found: {$totalArticles}";
            
            if ($totalArticles === 0) {
                $results['warnings'][] = "No articles found in legacy database";
                return;
            }
            
            // Sample articles for validation
            $sampleSize = min(100, $totalArticles);
            $articles = $this->legacyDb->getArticles($sampleSize);
            
            $issues = [
                'missing_title' => 0,
                'missing_content' => 0,
                'invalid_dates' => 0,
                'missing_slugs' => 0,
            ];
            
            foreach ($articles as $article) {
                if (empty($article->title)) {
                    $issues['missing_title']++;
                }
                
                if (empty($article->content)) {
                    $issues['missing_content']++;
                }
                
                if (!empty($article->created_at) && !$this->isValidDate($article->created_at)) {
                    $issues['invalid_dates']++;
                }
                
                if (empty($article->slug) && !empty($article->title)) {
                    $issues['missing_slugs']++;
                }
            }
            
            // Report issues
            foreach ($issues as $issue => $count) {
                if ($count > 0) {
                    $percentage = round(($count / $sampleSize) * 100, 2);
                    
                    if ($percentage > 10) {
                        $results['errors'][] = "High rate of {$issue}: {$count} articles ({$percentage}%) in sample";
                    } elseif ($percentage > 5) {
                        $results['warnings'][] = "Moderate rate of {$issue}: {$count} articles ({$percentage}%) in sample";
                    } else {
                        $results['info'][] = "Low rate of {$issue}: {$count} articles ({$percentage}%) in sample";
                    }
                }
            }
            
        } catch (\Exception $e) {
            $results['errors'][] = "Failed to validate article data: " . $e->getMessage();
        }
    }

    /**
     * Check for orphaned records
     */
    protected function checkOrphanedRecords(array &$results): void
    {
        try {
            // Check for articles with invalid category IDs (if categories exist)
            $categoryTable = config('wlcms.legacy.category_table');
            if ($categoryTable && in_array($categoryTable, $this->legacyDb->getTableList())) {
                $orphanedArticles = $this->legacyDb->query("
                    SELECT COUNT(*) as count 
                    FROM {$config['article_table']} a
                    LEFT JOIN {$categoryTable} c ON a.category_id = c.id
                    WHERE a.category_id IS NOT NULL 
                    AND a.category_id != 0 
                    AND c.id IS NULL
                ");
                
                $count = $orphanedArticles->first()->count ?? 0;
                if ($count > 0) {
                    $results['warnings'][] = "Found {$count} articles with invalid category references";
                }
            }
            
        } catch (\Exception $e) {
            $results['warnings'][] = "Could not check for orphaned records: " . $e->getMessage();
        }
    }

    /**
     * Check for duplicate data
     */
    protected function checkDuplicateData(array &$results): void
    {
        try {
            $articleTable = config('wlcms.legacy.article_table', 'articles');
            
            // Check for duplicate titles
            $duplicateTitles = $this->legacyDb->query("
                SELECT title, COUNT(*) as count 
                FROM {$articleTable} 
                WHERE title IS NOT NULL AND title != ''
                GROUP BY title 
                HAVING count > 1
                LIMIT 10
            ");
            
            if ($duplicateTitles->count() > 0) {
                $results['warnings'][] = "Found {$duplicateTitles->count()} duplicate article titles";
            }
            
            // Check for duplicate slugs
            $duplicateSlugs = $this->legacyDb->query("
                SELECT slug, COUNT(*) as count 
                FROM {$articleTable} 
                WHERE slug IS NOT NULL AND slug != ''
                GROUP BY slug 
                HAVING count > 1
                LIMIT 10
            ");
            
            if ($duplicateSlugs->count() > 0) {
                $results['warnings'][] = "Found {$duplicateSlugs->count()} duplicate article slugs";
            }
            
        } catch (\Exception $e) {
            $results['warnings'][] = "Could not check for duplicate data: " . $e->getMessage();
        }
    }

    /**
     * Check data consistency
     */
    protected function checkDataConsistency(array &$results): void
    {
        try {
            $articleTable = config('wlcms.legacy.article_table', 'articles');
            
            // Check for articles with future publication dates
            $futureArticles = $this->legacyDb->query("
                SELECT COUNT(*) as count 
                FROM {$articleTable} 
                WHERE published_at > NOW()
            ");
            
            $count = $futureArticles->first()->count ?? 0;
            if ($count > 0) {
                $results['info'][] = "Found {$count} articles with future publication dates";
            }
            
        } catch (\Exception $e) {
            $results['warnings'][] = "Could not check data consistency: " . $e->getMessage();
        }
    }

    /**
     * Validate performance characteristics
     */
    protected function validatePerformance(): array
    {
        $results = ['warnings' => [], 'recommendations' => [], 'info' => []];
        
        try {
            $articleTable = config('wlcms.legacy.article_table', 'articles');
            $totalArticles = $this->legacyDb->getArticleCount();
            
            // Check table size for performance recommendations
            if ($totalArticles > 10000) {
                $results['recommendations'][] = "Large table detected ({$totalArticles} articles). Consider implementing batch processing.";
            }
            
            if ($totalArticles > 50000) {
                $results['recommendations'][] = "Very large table. Consider migration in smaller batches to avoid memory issues.";
            }
            
            // Check for missing indexes (basic check)
            $structure = $this->legacyDb->getTableStructure($articleTable);
            $hasIdIndex = false;
            $hasStatusIndex = false;
            
            foreach ($structure as $column) {
                if ($column['name'] === 'id' && str_contains($column['key'], 'PRI')) {
                    $hasIdIndex = true;
                }
                if ($column['name'] === 'status' && !empty($column['key'])) {
                    $hasStatusIndex = true;
                }
            }
            
            if (!$hasIdIndex) {
                $results['warnings'][] = "No primary key found on ID column";
            }
            
            if (!$hasStatusIndex && $totalArticles > 1000) {
                $results['recommendations'][] = "Consider adding an index on status column for better performance";
            }
            
        } catch (\Exception $e) {
            $results['warnings'][] = "Could not complete performance validation: " . $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Validate individual article data before migration
     */
    public function validateArticleForMigration(object $article): array
    {
        $errors = [];
        $warnings = [];
        
        // Required fields
        if (empty($article->title)) {
            $errors[] = "Article title is required";
        }
        
        if (empty($article->content)) {
            $warnings[] = "Article content is empty";
        }
        
        // Length validations
        if (!empty($article->title) && strlen($article->title) > 255) {
            $errors[] = "Article title exceeds 255 characters";
        }
        
        // Date validations
        if (!empty($article->created_at) && !$this->isValidDate($article->created_at)) {
            $warnings[] = "Invalid created_at date format";
        }
        
        if (!empty($article->published_at) && !$this->isValidDate($article->published_at)) {
            $warnings[] = "Invalid published_at date format";
        }
        
        // URL/slug validation
        if (!empty($article->slug) && !$this->isValidSlug($article->slug)) {
            $warnings[] = "Article slug contains invalid characters";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Clean and prepare data for migration
     */
    public function cleanDataForMigration(object $article): object
    {
        $cleaned = clone $article;
        
        // Clean title
        if (!empty($cleaned->title)) {
            $cleaned->title = trim(strip_tags($cleaned->title));
            $cleaned->title = substr($cleaned->title, 0, 255);
        }
        
        // Clean content
        if (!empty($cleaned->content)) {
            $cleaned->content = $this->cleanHtmlContent($cleaned->content);
        }
        
        // Clean slug
        if (!empty($cleaned->slug)) {
            $cleaned->slug = $this->sanitizeSlug($cleaned->slug);
        }
        
        // Normalize dates
        if (!empty($cleaned->created_at)) {
            $cleaned->created_at = $this->normalizeDate($cleaned->created_at);
        }
        
        if (!empty($cleaned->published_at)) {
            $cleaned->published_at = $this->normalizeDate($cleaned->published_at);
        }
        
        return $cleaned;
    }

    /**
     * Initialize default validation rules
     */
    protected function initializeDefaultRules(): void
    {
        $this->validationRules = [
            'articles' => [
                'id' => ['required' => true, 'type' => ['int', 'bigint']],
                'title' => ['required' => true, 'type' => ['varchar', 'text']],
                'content' => ['required' => false, 'type' => ['text', 'longtext']],
                'slug' => ['required' => false, 'type' => ['varchar']],
                'status' => ['required' => false, 'type' => ['varchar', 'enum']],
                'created_at' => ['required' => false, 'type' => ['datetime', 'timestamp']],
                'updated_at' => ['required' => false, 'type' => ['datetime', 'timestamp']],
            ],
        ];
    }

    /**
     * Utility methods
     */
    protected function isValidDate($date): bool
    {
        try {
            if (is_numeric($date)) {
                return $date > 0;
            }
            return strtotime($date) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9\-_]+$/i', $slug);
    }

    protected function cleanHtmlContent(string $content): string
    {
        // Remove dangerous HTML
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
        $content = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content);
        
        return trim($content);
    }

    protected function sanitizeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9\-_]/i', '-', $slug);
    }

    protected function normalizeDate($date): ?string
    {
        try {
            if (is_numeric($date)) {
                return date('Y-m-d H:i:s', $date);
            }
            return date('Y-m-d H:i:s', strtotime($date));
        } catch (\Exception $e) {
            return null;
        }
    }
}