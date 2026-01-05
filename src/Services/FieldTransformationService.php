<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;

class FieldTransformationService
{
    protected array $transformers = [];

    public function __construct()
    {
        $this->registerDefaultTransformers();
    }

    /**
     * Transform content field with HTML cleanup and standardization
     */
    public function transformContent(string $content): string
    {
        // Remove unwanted HTML tags
        $content = $this->cleanHtml($content);
        
        // Convert legacy image paths
        $content = $this->convertImagePaths($content);
        
        // Convert legacy links
        $content = $this->convertLegacyLinks($content);
        
        // Standardize formatting
        $content = $this->standardizeFormatting($content);
        
        return trim($content);
    }

    /**
     * Clean and standardize text content
     */
    public function cleanText(string $text): string
    {
        // Remove HTML tags
        $text = strip_tags($text);
        
        // Convert HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim
        $text = trim($text);
        
        return $text;
    }

    /**
     * Transform field based on type and rules
     */
    public function transformField($value, string $targetField, string $sourceField, array $rules = [])
    {
        if (is_null($value)) {
            return null;
        }

        // Apply registered transformers
        if (isset($this->transformers[$targetField])) {
            $transformer = $this->transformers[$targetField];
            return $transformer($value, $rules);
        }

        // Default field-specific transformations
        switch ($targetField) {
            case 'title':
            case 'meta_title':
                return $this->transformTitle($value, $rules);
            
            case 'content':
                return $this->transformContent($value);
            
            case 'summary':
            case 'excerpt':
            case 'meta_description':
                return $this->transformSummary($value, $rules);
            
            case 'slug':
                return $this->transformSlug($value);
            
            case 'tags':
            case 'categories':
                return $this->transformTags($value, $rules);
            
            case 'published_at':
            case 'created_at':
            case 'updated_at':
                return $this->transformDate($value);
            
            case 'status':
                return $this->transformStatus($value, $rules);
            
            case 'author':
            case 'author_name':
                return $this->transformAuthor($value, $rules);
            
            case 'featured_image':
            case 'image':
                return $this->transformImagePath($value);
            
            case 'price':
            case 'cost':
                return $this->transformPrice($value);
            
            case 'weight':
            case 'dimension':
                return $this->transformNumeric($value);
            
            case 'email':
                return $this->transformEmail($value);
            
            case 'url':
            case 'website':
                return $this->transformUrl($value);
            
            case 'phone':
                return $this->transformPhone($value);
            
            default:
                return $this->transformDefault($value, $rules);
        }
    }

    /**
     * Transform title with proper capitalization
     */
    protected function transformTitle(string $title, array $rules = []): string
    {
        $title = $this->cleanText($title);
        
        // Apply title case if requested
        if ($rules['title_case'] ?? false) {
            $title = Str::title($title);
        }
        
        // Limit length if specified
        if (!empty($rules['max_length'])) {
            $title = Str::limit($title, $rules['max_length'], '');
        }
        
        return $title;
    }

    /**
     * Transform summary/excerpt
     */
    protected function transformSummary(string $summary, array $rules = []): string
    {
        $summary = $this->cleanText($summary);
        
        // Auto-generate from content if empty and content is provided
        if (empty($summary) && !empty($rules['auto_generate_from_content'])) {
            $summary = Str::limit(strip_tags($rules['auto_generate_from_content']), 160);
        }
        
        // Limit length
        $maxLength = $rules['max_length'] ?? 300;
        $summary = Str::limit($summary, $maxLength, '...');
        
        return $summary;
    }

    /**
     * Transform slug with proper formatting
     */
    protected function transformSlug(string $slug): string
    {
        return Str::slug($slug);
    }

    /**
     * Transform tags/categories
     */
    protected function transformTags($tags, array $rules = []): array
    {
        if (is_string($tags)) {
            // Split by comma or other delimiter
            $delimiter = $rules['delimiter'] ?? ',';
            $tags = explode($delimiter, $tags);
        }
        
        if (!is_array($tags)) {
            return [];
        }
        
        // Clean and filter tags
        $cleanTags = [];
        foreach ($tags as $tag) {
            $tag = trim($this->cleanText($tag));
            if (!empty($tag)) {
                $cleanTags[] = $tag;
            }
        }
        
        return array_unique($cleanTags);
    }

    /**
     * Transform date field
     */
    protected function transformDate($date): ?Carbon
    {
        if (is_null($date)) {
            return null;
        }
        
        try {
            if (is_numeric($date)) {
                return Carbon::createFromTimestamp($date);
            }
            
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform status with mapping
     */
    protected function transformStatus($status, array $rules = []): string
    {
        $statusMap = $rules['status_map'] ?? [
            '1' => 'published',
            '0' => 'draft',
            'active' => 'published',
            'inactive' => 'draft',
            'pending' => 'pending',
            'archived' => 'archived',
        ];
        
        $status = strtolower(trim($status));
        
        return $statusMap[$status] ?? 'draft';
    }

    /**
     * Transform author field
     */
    protected function transformAuthor($author, array $rules = []): string
    {
        if (is_numeric($author) && !empty($rules['user_mapping'])) {
            // Map user ID to name
            $userMap = $rules['user_mapping'];
            return $userMap[$author] ?? 'Unknown Author';
        }
        
        return $this->cleanText($author);
    }

    /**
     * Transform image path
     */
    protected function transformImagePath(string $path): string
    {
        // Convert relative paths to absolute
        if (!str_starts_with($path, 'http') && !str_starts_with($path, '/')) {
            $basePath = config('wlcms.legacy.image_base_path', '/images/');
            $path = $basePath . ltrim($path, '/');
        }
        
        return $path;
    }

    /**
     * Transform price field
     */
    protected function transformPrice($price): ?float
    {
        if (is_null($price)) {
            return null;
        }
        
        // Remove currency symbols and formatting
        $price = preg_replace('/[^\d\.]/', '', $price);
        
        return is_numeric($price) ? floatval($price) : null;
    }

    /**
     * Transform numeric field
     */
    protected function transformNumeric($value): ?float
    {
        if (is_null($value)) {
            return null;
        }
        
        return is_numeric($value) ? floatval($value) : null;
    }

    /**
     * Transform email field
     */
    protected function transformEmail(string $email): ?string
    {
        $email = strtolower(trim($email));
        
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Transform URL field
     */
    protected function transformUrl(string $url): string
    {
        $url = trim($url);
        
        // Add protocol if missing
        if (!preg_match('/^https?:\/\//', $url) && !empty($url)) {
            $url = 'http://' . $url;
        }
        
        return $url;
    }

    /**
     * Transform phone number
     */
    protected function transformPhone(string $phone): string
    {
        // Remove all non-numeric characters except + and -
        $phone = preg_replace('/[^0-9\+\-\(\)\s]/', '', $phone);
        
        return trim($phone);
    }

    /**
     * Default transformation
     */
    protected function transformDefault($value, array $rules = [])
    {
        if (is_string($value)) {
            return trim($value);
        }
        
        return $value;
    }

    /**
     * Clean HTML content
     */
    protected function cleanHtml(string $html): string
    {
        // Allowed HTML tags
        $allowedTags = config('wlcms.legacy.allowed_html_tags', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote', 'table', 'tr', 'td', 'th', 'thead', 'tbody',
        ]);
        
        // Convert to allowed tags string
        $allowedTagsString = '<' . implode('><', $allowedTags) . '>';
        
        // Strip unwanted tags
        $html = strip_tags($html, $allowedTagsString);
        
        // Remove script and style content
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);
        
        return $html;
    }

    /**
     * Convert legacy image paths to new paths
     */
    protected function convertImagePaths(string $content): string
    {
        $legacyImagePath = config('wlcms.legacy.image_path_pattern', '/uploads/');
        $newImagePath = config('wlcms.media.public_path', '/media/');
        
        return str_replace($legacyImagePath, $newImagePath, $content);
    }

    /**
     * Convert legacy links to new CMS links
     */
    protected function convertLegacyLinks(string $content): string
    {
        $legacyUrlPattern = config('wlcms.legacy.url_pattern', '/article.php?id={id}');
        
        // This would require more complex logic to map legacy URLs to new CMS URLs
        // For now, just placeholder
        return $content;
    }

    /**
     * Standardize HTML formatting
     */
    protected function standardizeFormatting(string $content): string
    {
        // Convert <b> to <strong>
        $content = preg_replace('/<b(\s[^>]*)?>/i', '<strong>', $content);
        $content = preg_replace('/<\/b>/i', '</strong>', $content);
        
        // Convert <i> to <em>
        $content = preg_replace('/<i(\s[^>]*)?>/i', '<em>', $content);
        $content = preg_replace('/<\/i>/i', '</em>', $content);
        
        // Remove empty paragraphs
        $content = preg_replace('/<p[^>]*>(\s|&nbsp;)*<\/p>/i', '', $content);
        
        // Clean up excessive line breaks
        $content = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br><br>', $content);
        
        return $content;
    }

    /**
     * Register a custom transformer for a specific field
     */
    public function registerTransformer(string $fieldName, callable $transformer): void
    {
        $this->transformers[$fieldName] = $transformer;
    }

    /**
     * Register default transformers
     */
    protected function registerDefaultTransformers(): void
    {
        // Register any default custom transformers here
        
        // Example: Custom product code transformer
        $this->registerTransformer('product_code', function ($value, $rules) {
            return strtoupper(trim($value));
        });
    }

    /**
     * Validate transformed data
     */
    public function validateTransformedData(array $data, array $rules = []): array
    {
        $errors = [];
        
        // Required field validation
        $requiredFields = $rules['required'] ?? ['title'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Required field '{$field}' is missing or empty";
            }
        }
        
        // Length validation
        if (!empty($data['title']) && strlen($data['title']) > 255) {
            $errors[] = "Title exceeds maximum length of 255 characters";
        }
        
        if (!empty($data['slug']) && strlen($data['slug']) > 255) {
            $errors[] = "Slug exceeds maximum length of 255 characters";
        }
        
        // URL validation
        if (!empty($data['slug']) && !preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
            $errors[] = "Slug contains invalid characters";
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        return $errors;
    }
}