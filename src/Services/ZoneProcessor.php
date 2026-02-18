<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Support\Str;

class ZoneProcessor
{
    /**
     * Shortcode parser instance.
     *
     * @var ShortcodeParser|null
     */
    protected ?ShortcodeParser $shortcodeParser = null;

    /**
     * Constructor.
     *
     * @param ShortcodeParser|null $shortcodeParser
     */
    public function __construct(?ShortcodeParser $shortcodeParser = null)
    {
        $this->shortcodeParser = $shortcodeParser;
    }

    /**
     * Process zone data based on zone type.
     *
     * @param string $type Zone type
     * @param mixed $data Raw zone data
     * @param array $config Zone configuration
     * @return mixed Processed zone data
     */
    public function process(string $type, mixed $data, array $config = []): mixed
    {
        $method = 'process' . Str::studly($type);

        if (method_exists($this, $method)) {
            return $this->$method($data, $config);
        }

        // Default: return data as-is
        return $data;
    }

    /**
     * Render zone data to HTML.
     *
     * @param string $type Zone type
     * @param mixed $data Processed zone data
     * @param array $config Zone configuration
     * @return string Rendered HTML
     */
    public function render(string $type, mixed $data, array $config = []): string
    {
        $method = 'render' . Str::studly($type);

        if (method_exists($this, $method)) {
            return $this->$method($data, $config);
        }

        // Default: return data as string
        return is_string($data) ? $data : '';
    }

    /**
     * Process rich text zone.
     *
     * @param mixed $data Rich text content
     * @param array $config Zone configuration
     * @return string
     */
    protected function processRichText(mixed $data, array $config = []): string
    {
        if (!is_string($data)) {
            return '';
        }

        // Return the HTML content (already processed by Tiptap editor)
        return $data;
    }

    /**
     * Render rich text zone.
     *
     * @param mixed $data Processed rich text data
     * @param array $config Zone configuration
     * @return string
     */
    protected function renderRichText(mixed $data, array $config = []): string
    {
        $content = $data ?? '';
        
        // Parse shortcodes if parser is available
        if ($this->shortcodeParser && $this->shortcodeParser->hasShortcodes($content)) {
            $content = $this->shortcodeParser->parse($content);
        }
        
        return $content;
    }

    /**
     * Process conditional zone.
     *
     * @param mixed $data Conditional zone data
     * @param array $config Zone configuration
     * @return array
     */
    protected function processConditional(mixed $data, array $config = []): array
    {
        if (!is_array($data)) {
            return ['content' => '', 'conditions' => []];
        }

        return [
            'content' => $data['content'] ?? '',
            'conditions' => $data['conditions'] ?? [],
        ];
    }

    /**
     * Render conditional zone.
     *
     * @param mixed $data Processed conditional data
     * @param array $config Zone configuration
     * @return string
     */
    protected function renderConditional(mixed $data, array $config = []): string
    {
        if (!is_array($data)) {
            return '';
        }

        // Check if conditions are met (to be implemented based on condition types)
        // For now, just return content if conditions exist
        return $data['content'] ?? '';
    }

    /**
     * Process repeater zone.
     *
     * @param mixed $data Repeater zone data (array of items)
     * @param array $config Zone configuration
     * @return array
     */
    protected function processRepeater(mixed $data, array $config = []): array
    {
        if (!is_array($data)) {
            return [];
        }

        // Ensure it's a sequential array
        return array_values($data);
    }

    /**
     * Render repeater zone.
     *
     * @param mixed $data Processed repeater data
     * @param array $config Zone configuration
     * @return string
     */
    protected function renderRepeater(mixed $data, array $config = []): string
    {
        if (!is_array($data)) {
            return '';
        }

        // Return JSON encoded for use in templates
        return json_encode($data);
    }

    /**
     * Process media gallery zone.
     *
     * @param mixed $data Media gallery data (array of media items)
     * @param array $config Zone configuration
     * @return array
     */
    protected function processMediaGallery(mixed $data, array $config = []): array
    {
        if (!is_array($data)) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'url' => $item['url'] ?? '',
                'alt' => $item['alt'] ?? '',
                'caption' => $item['caption'] ?? '',
                'thumbnail' => $item['thumbnail'] ?? $item['url'] ?? '',
            ];
        }, $data);
    }

    /**
     * Render media gallery zone.
     *
     * @param mixed $data Processed media gallery data
     * @param array $config Zone configuration
     * @return string
     */
    protected function renderMediaGallery(mixed $data, array $config = []): string
    {
        if (!is_array($data) || empty($data)) {
            return '';
        }

        $html = '<div class="media-gallery">';

        foreach ($data as $item) {
            $html .= '<div class="media-item">';
            $html .= '<img src="' . htmlspecialchars($item['url']) . '" ';
            $html .= 'alt="' . htmlspecialchars($item['alt']) . '">';

            if (!empty($item['caption'])) {
                $html .= '<p class="caption">' . htmlspecialchars($item['caption']) . '</p>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Process file list zone.
     *
     * @param mixed $data File list data
     * @param array $config Zone configuration
     * @return array
     */
    protected function processFileList(mixed $data, array $config = []): array
    {
        if (!is_array($data)) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'url' => $item['url'] ?? '',
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'size' => $item['size'] ?? null,
                'type' => $item['type'] ?? 'file',
            ];
        }, $data);
    }

    /**
     * Render file list zone.
     *
     * @param mixed $data Processed file list data
     * @param array $config Zone configuration
     * @return string
     */
    protected function renderFileList(mixed $data, array $config = []): string
    {
        if (!is_array($data) || empty($data)) {
            return '';
        }

        $html = '<ul class="file-list">';

        foreach ($data as $file) {
            $html .= '<li class="file-item">';
            $html .= '<a href="' . htmlspecialchars($file['url']) . '" target="_blank">';
            $html .= htmlspecialchars($file['title']);
            $html .= '</a>';

            if (!empty($file['description'])) {
                $html .= '<p class="file-description">' . htmlspecialchars($file['description']) . '</p>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Process link list zone.
     *
     * @param mixed $data Link list data
     * @param array $config Zone configuration
     * @return array
     */
    protected function processLinkList(mixed $data, array $config = []): array
    {
        if (!is_array($data)) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'url' => $item['url'] ?? '',
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'target' => $item['target'] ?? '_self',
            ];
        }, $data);
    }

    /**
     * Render link list zone.
     *
     * @param mixed $data Processed link list data
     * @param array $config Zone configuration
     * @return string
     */
    protected function renderLinkList(mixed $data, array $config = []): string
    {
        if (!is_array($data) || empty($data)) {
            return '';
        }

        $html = '<ul class="link-list">';

        foreach ($data as $link) {
            $html .= '<li class="link-item">';
            $html .= '<a href="' . htmlspecialchars($link['url']) . '" ';
            $html .= 'target="' . htmlspecialchars($link['target']) . '">';
            $html .= htmlspecialchars($link['title']);
            $html .= '</a>';

            if (!empty($link['description'])) {
                $html .= '<p class="link-description">' . htmlspecialchars($link['description']) . '</p>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Process form embed zone.
     *
     * @param mixed $data Form embed data
     * @param array $config Zone configuration
     * @return array
     */
    protected function processFormEmbed(mixed $data, array $config = []): array
    {
        if (!is_array($data)) {
            return ['type' => 'none', 'data' => null];
        }

        return [
            'type' => $data['type'] ?? 'none', // built-in, custom, embed
            'form_id' => $data['form_id'] ?? null,
            'embed_code' => $data['embed_code'] ?? '',
            'settings' => $data['settings'] ?? [],
        ];
    }

    /**
     * Render form embed zone.
     *
     * @param mixed $data Processed form embed data
     * @param array $config Zone configuration
     * @return string
     */
    protected function renderFormEmbed(mixed $data, array $config = []): string
    {
        if (!is_array($data)) {
            return '';
        }

        $embedType = $data['embed_type'] ?? 'built-in';
        $embedCode = $data['embed_code'] ?? '';
        $formId = $data['form_id'] ?? '';

        // Handle external embed code (raw HTML/iframe)
        if ($embedType === 'external' && !empty($embedCode)) {
            return $embedCode;
        }

        // Handle shortcode (either from built-in selector or manual entry)
        if (!empty($embedCode) && $this->shortcodeParser) {
            // Check if it's already a shortcode
            if ($this->shortcodeParser->hasShortcodes($embedCode)) {
                return $this->shortcodeParser->parse($embedCode);
            }
        }

        // Handle built-in form by ID
        if (!empty($formId)) {
            $formRenderer = app(\Westlinks\Wlcms\Services\FormRenderer::class);
            return $formRenderer->render($formId);
        }

        return '<!-- No form configured -->';
    }

    /**
     * Validate zone data against zone configuration.
     *
     * @param string $type Zone type
     * @param mixed $data Zone data
     * @param array $config Zone configuration
     * @return array Validation errors
     */
    public function validate(string $type, mixed $data, array $config = []): array
    {
        $errors = [];

        // Check if required
        if (($config['required'] ?? false) && empty($data)) {
            $errors[] = "The {$config['label']} zone is required.";
        }

        // Type-specific validation
        $method = 'validate' . Str::studly($type);

        if (method_exists($this, $method)) {
            $typeErrors = $this->$method($data, $config);
            $errors = array_merge($errors, $typeErrors);
        }

        return $errors;
    }

    /**
     * Validate repeater zone.
     *
     * @param mixed $data Repeater data
     * @param array $config Zone configuration
     * @return array Validation errors
     */
    protected function validateRepeater(mixed $data, array $config = []): array
    {
        $errors = [];

        if (!is_array($data)) {
            $errors[] = "Repeater data must be an array.";
        }

        return $errors;
    }

    /**
     * Validate media gallery zone.
     *
     * @param mixed $data Media gallery data
     * @param array $config Zone configuration
     * @return array Validation errors
     */
    protected function validateMediaGallery(mixed $data, array $config = []): array
    {
        $errors = [];

        if (!is_array($data)) {
            $errors[] = "Media gallery data must be an array.";
        }

        return $errors;
    }
}
