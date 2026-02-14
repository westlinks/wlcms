<?php

namespace Westlinks\Wlcms\Services;

use Westlinks\Wlcms\Models\Template;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class TemplateManager
{
    /**
     * Registered templates (before persisting to database).
     *
     * @var array
     */
    protected static array $registered = [];

    /**
     * Cache key for templates.
     */
    protected const CACHE_KEY = 'wlcms_templates';

    /**
     * Cache duration in seconds (1 hour).
     */
    protected const CACHE_DURATION = 3600;

    /**
     * Register a new template.
     *
     * @param string $identifier Unique template identifier
     * @param array $config Template configuration
     * @return void
     */
    public static function register(string $identifier, array $config): void
    {
        // Validate required configuration keys
        $required = ['name', 'view', 'zones'];
        foreach ($required as $key) {
            if (!isset($config[$key])) {
                throw new \InvalidArgumentException("Template configuration must include '{$key}' for identifier '{$identifier}'");
            }
        }

        // Set defaults
        $config = array_merge([
            'description' => null,
            'preview' => null,
            'features' => [],
            'settings_schema' => [],
            'category' => 'content',
            'version' => '1.0',
            'is_default' => true,
            'active' => true,
            'sort_order' => count(static::$registered),
        ], $config);

        static::$registered[$identifier] = [
            'identifier' => $identifier,
            'name' => $config['name'],
            'description' => $config['description'],
            'preview_image' => $config['preview'],
            'zones' => $config['zones'],
            'features' => $config['features'],
            'settings_schema' => $config['settings_schema'],
            'view_path' => $config['view'],
            'category' => $config['category'],
            'version' => $config['version'],
            'is_default' => $config['is_default'],
            'active' => $config['active'],
            'sort_order' => $config['sort_order'],
        ];
    }

    /**
     * Persist registered templates to database.
     *
     * @return void
     */
    public static function persistRegisteredTemplates(): void
    {
        foreach (static::$registered as $identifier => $config) {
            Template::updateOrCreate(
                ['identifier' => $identifier],
                $config
            );
        }

        // Clear cache after persisting
        static::clearCache();
    }

    /**
     * Get all registered templates (from memory, not database).
     *
     * @return array
     */
    public static function getRegistered(): array
    {
        return static::$registered;
    }

    /**
     * Get all active templates from database.
     *
     * @param bool $useCache Whether to use cache
     * @return Collection
     */
    public static function getActive(bool $useCache = true): Collection
    {
        if ($useCache) {
            return Cache::remember(static::CACHE_KEY . '_active', static::CACHE_DURATION, function () {
                return Template::active()->ordered()->get();
            });
        }

        return Template::active()->ordered()->get();
    }

    /**
     * Get all templates from database.
     *
     * @param bool $useCache Whether to use cache
     * @return Collection
     */
    public static function getAll(bool $useCache = true): Collection
    {
        if ($useCache) {
            return Cache::remember(static::CACHE_KEY . '_all', static::CACHE_DURATION, function () {
                return Template::ordered()->get();
            });
        }

        return Template::ordered()->get();
    }

    /**
     * Get templates by category.
     *
     * @param string $category Template category
     * @param bool $activeOnly Whether to get only active templates
     * @return Collection
     */
    public static function getByCategory(string $category, bool $activeOnly = true): Collection
    {
        $query = Template::byCategory($category);

        if ($activeOnly) {
            $query->active();
        }

        return $query->ordered()->get();
    }

    /**
     * Get a specific template by identifier.
     *
     * @param string $identifier Template identifier
     * @return Template|null
     */
    public static function get(string $identifier): ?Template
    {
        return Cache::remember(static::CACHE_KEY . "_{$identifier}", static::CACHE_DURATION, function () use ($identifier) {
            return Template::where('identifier', $identifier)->first();
        });
    }

    /**
     * Get template or fail.
     *
     * @param string $identifier Template identifier
     * @return Template
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function getOrFail(string $identifier): Template
    {
        $template = static::get($identifier);

        if (!$template) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Template with identifier '{$identifier}' not found."
            );
        }

        return $template;
    }

    /**
     * Check if a template exists.
     *
     * @param string $identifier Template identifier
     * @return bool
     */
    public static function exists(string $identifier): bool
    {
        return static::get($identifier) !== null;
    }

    /**
     * Validate template settings against schema.
     *
     * @param Template $template Template instance
     * @param array $settings Settings to validate
     * @return array Validated settings
     * @throws ValidationException
     */
    public static function validateSettings(Template $template, array $settings): array
    {
        $schema = $template->settings_schema ?? [];
        $validated = [];
        $errors = [];

        foreach ($schema as $key => $field) {
            $value = $settings[$key] ?? null;

            // Check required fields
            if (($field['required'] ?? false) && empty($value)) {
                $errors[$key] = "The {$field['label']} field is required.";
                continue;
            }

            // Use default if no value provided
            if ($value === null && isset($field['default'])) {
                $value = $field['default'];
            }

            // Type validation
            if ($value !== null) {
                $validated[$key] = static::validateSettingValue($key, $value, $field, $errors);
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    /**
     * Validate a single setting value.
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param array $field Field schema
     * @param array &$errors Errors array (by reference)
     * @return mixed Validated value
     */
    protected static function validateSettingValue(string $key, mixed $value, array $field, array &$errors): mixed
    {
        $type = $field['type'] ?? 'text';

        switch ($type) {
            case 'number':
                if (!is_numeric($value)) {
                    $errors[$key] = "The {$field['label']} must be a number.";
                    return null;
                }
                return (int) $value;

            case 'select':
                $options = array_keys($field['options'] ?? []);
                if (!in_array($value, $options)) {
                    $errors[$key] = "The {$field['label']} must be one of: " . implode(', ', $options);
                    return null;
                }
                return $value;

            case 'date':
                try {
                    return \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (\Exception $e) {
                    $errors[$key] = "The {$field['label']} must be a valid date.";
                    return null;
                }

            default:
                return $value;
        }
    }

    /**
     * Validate zones data against template zones configuration.
     *
     * @param Template $template Template instance
     * @param array $zonesData Zones data to validate
     * @return array Validation errors
     */
    public static function validateZones(Template $template, array $zonesData): array
    {
        return $template->validateZonesData($zonesData);
    }

    /**
     * Clear template cache.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $templates = Template::all();

        Cache::forget(static::CACHE_KEY . '_active');
        Cache::forget(static::CACHE_KEY . '_all');

        foreach ($templates as $template) {
            Cache::forget(static::CACHE_KEY . "_{$template->identifier}");
        }
    }

    /**
     * Get available template categories.
     *
     * @return array
     */
    public static function getCategories(): array
    {
        return Template::getCategories();
    }

    /**
     * Deactivate a template.
     *
     * @param string $identifier Template identifier
     * @return bool
     */
    public static function deactivate(string $identifier): bool
    {
        $template = static::get($identifier);

        if (!$template) {
            return false;
        }

        $template->update(['active' => false]);
        static::clearCache();

        return true;
    }

    /**
     * Activate a template.
     *
     * @param string $identifier Template identifier
     * @return bool
     */
    public static function activate(string $identifier): bool
    {
        $template = static::get($identifier);

        if (!$template) {
            return false;
        }

        $template->update(['active' => true]);
        static::clearCache();

        return true;
    }
}
