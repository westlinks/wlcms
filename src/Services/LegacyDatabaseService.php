<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class LegacyDatabaseService
{
    protected string $connectionName;
    protected array $config;

    public function __construct()
    {
        $this->connectionName = config('wlcms.legacy.database_connection', 'legacy');
        $this->config = config('wlcms.legacy', []);
    }

    /**
     * Get the legacy database connection
     */
    public function getConnection(): ConnectionInterface
    {
        return DB::connection($this->connectionName);
    }

    /**
     * Test the legacy database connection
     */
    public function testConnection(): array
    {
        try {
            $connection = $this->getConnection();
            $connection->getPdo();
            
            // Test basic connectivity
            $result = $connection->select('SELECT 1 as test');
            
            // Get database info
            $dbName = $connection->getDatabaseName();
            $tables = $this->getTableList();
            
            return [
                'status' => 'success',
                'message' => 'Legacy database connection successful',
                'database' => $dbName,
                'table_count' => count($tables),
                'tables' => $tables,
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Legacy database connection failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of tables in the legacy database
     */
    public function getTableList(): array
    {
        try {
            $connection = $this->getConnection();
            $tables = $connection->select('SHOW TABLES');
            
            $tableNames = [];
            foreach ($tables as $table) {
                $tableArray = (array) $table;
                $tableNames[] = array_values($tableArray)[0];
            }
            
            return $tableNames;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get table structure information
     */
    public function getTableStructure(string $tableName): array
    {
        try {
            $connection = $this->getConnection();
            $columns = $connection->select("DESCRIBE {$tableName}");
            
            $structure = [];
            foreach ($columns as $column) {
                $structure[] = [
                    'name' => $column->Field,
                    'type' => $column->Type,
                    'null' => $column->Null === 'YES',
                    'key' => $column->Key,
                    'default' => $column->Default,
                    'extra' => $column->Extra,
                ];
            }
            
            return $structure;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get legacy articles with pagination
     */
    public function getArticles(int $limit = 50, int $offset = 0, array $filters = []): Collection
    {
        try {
            $tableName = $this->config['article_table'] ?? 'articles';
            $query = $this->getConnection()->table($tableName);
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (!empty($filters['category'])) {
                $query->where('category_id', $filters['category']);
            }
            
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            
            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'LIKE', '%' . $filters['search'] . '%')
                      ->orWhere('content', 'LIKE', '%' . $filters['search'] . '%');
                });
            }
            
            return $query->offset($offset)
                        ->limit($limit)
                        ->orderBy('id')
                        ->get();
                        
        } catch (\Exception $e) {
            \Log::error('Failed to fetch legacy articles: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get single legacy article by ID
     */
    public function getArticle(int $id): ?object
    {
        try {
            $tableName = $this->config['article_table'] ?? 'articles';
            return $this->getConnection()
                       ->table($tableName)
                       ->where('id', $id)
                       ->first();
        } catch (\Exception $e) {
            \Log::error('Failed to fetch legacy article: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get article count with optional filters
     */
    public function getArticleCount(array $filters = []): int
    {
        try {
            $tableName = $this->config['article_table'] ?? 'articles';
            $query = $this->getConnection()->table($tableName);
            
            // Apply same filters as getArticles
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (!empty($filters['category'])) {
                $query->where('category_id', $filters['category']);
            }
            
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            
            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'LIKE', '%' . $filters['search'] . '%')
                      ->orWhere('content', 'LIKE', '%' . $filters['search'] . '%');
                });
            }
            
            return $query->count();
            
        } catch (\Exception $e) {
            \Log::error('Failed to count legacy articles: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get unmapped legacy articles
     */
    public function getUnmappedArticles(int $limit = 50): Collection
    {
        try {
            $tableName = $this->config['article_table'] ?? 'articles';
            $mappedIds = \Westlinks\Wlcms\Models\CmsLegacyArticleMapping::pluck('legacy_article_id');
            
            return $this->getConnection()
                       ->table($tableName)
                       ->whereNotIn('id', $mappedIds)
                       ->limit($limit)
                       ->orderBy('id')
                       ->get();
                       
        } catch (\Exception $e) {
            \Log::error('Failed to fetch unmapped articles: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get legacy categories
     */
    public function getCategories(): Collection
    {
        try {
            $tableName = $this->config['category_table'] ?? 'categories';
            return $this->getConnection()
                       ->table($tableName)
                       ->orderBy('name')
                       ->get();
        } catch (\Exception $e) {
            \Log::error('Failed to fetch legacy categories: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get legacy navigation items
     */
    public function getNavigationItems(): Collection
    {
        try {
            $tableName = $this->config['navigation_table'] ?? 'navigation';
            return $this->getConnection()
                       ->table($tableName)
                       ->orderBy('sort_order')
                       ->get();
        } catch (\Exception $e) {
            \Log::error('Failed to fetch legacy navigation: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Execute custom query on legacy database
     */
    public function query(string $sql, array $bindings = []): Collection
    {
        try {
            $results = $this->getConnection()->select($sql, $bindings);
            return collect($results);
        } catch (\Exception $e) {
            \Log::error('Legacy database query failed: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Setup legacy database connection configuration
     */
    public function setupConnection(array $connectionConfig): bool
    {
        try {
            // Dynamically configure the legacy connection
            Config::set("database.connections.{$this->connectionName}", [
                'driver' => $connectionConfig['driver'] ?? 'mysql',
                'host' => $connectionConfig['host'],
                'port' => $connectionConfig['port'] ?? 3306,
                'database' => $connectionConfig['database'],
                'username' => $connectionConfig['username'],
                'password' => $connectionConfig['password'],
                'charset' => $connectionConfig['charset'] ?? 'utf8mb4',
                'collation' => $connectionConfig['collation'] ?? 'utf8mb4_unicode_ci',
                'prefix' => $connectionConfig['prefix'] ?? '',
                'strict' => false,
                'engine' => null,
            ]);
            
            // Purge any existing connection
            DB::purge($this->connectionName);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to setup legacy connection: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if legacy database is properly configured
     */
    public function isConfigured(): bool
    {
        $requiredKeys = ['host', 'database', 'username'];
        $connectionConfig = config("database.connections.{$this->connectionName}", []);
        
        foreach ($requiredKeys as $key) {
            if (empty($connectionConfig[$key])) {
                return false;
            }
        }
        
        return true;
    }
}