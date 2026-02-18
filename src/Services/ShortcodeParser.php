<?php

namespace Westlinks\Wlcms\Services;

class ShortcodeParser
{
    /**
     * Registered shortcode handlers.
     *
     * @var array
     */
    protected array $handlers = [];

    /**
     * Form renderer instance.
     *
     * @var FormRenderer
     */
    protected FormRenderer $formRenderer;

    /**
     * Constructor.
     *
     * @param FormRenderer $formRenderer
     */
    public function __construct(FormRenderer $formRenderer)
    {
        $this->formRenderer = $formRenderer;

        // Register default shortcode handlers
        $this->registerDefaultHandlers();
    }

    /**
     * Register default shortcode handlers.
     *
     * @return void
     */
    protected function registerDefaultHandlers(): void
    {
        // Form shortcode: [form id="contact" class="my-form"]
        $this->register('form', function ($attributes) {
            $identifier = $attributes['id'] ?? null;

            if (!$identifier) {
                return '<!-- Form shortcode missing id attribute -->';
            }

            unset($attributes['id']);
            return $this->formRenderer->render($identifier, $attributes);
        });

        // Button shortcode: [button url="/path" text="Click Here" class="btn-primary"]
        $this->register('button', function ($attributes) {
            $url = $attributes['url'] ?? '#';
            $text = $attributes['text'] ?? 'Button';
            $class = $attributes['class'] ?? 'btn btn-primary';
            $target = isset($attributes['blank']) ? ' target="_blank" rel="noopener noreferrer"' : '';

            return sprintf(
                '<a href="%s" class="%s"%s>%s</a>',
                e($url),
                e($class),
                $target,
                e($text)
            );
        });

        // Alert shortcode: [alert type="info"]Your message here[/alert]
        $this->register('alert', function ($attributes, $content = '') {
            $type = $attributes['type'] ?? 'info';
            $classes = [
                'info' => 'bg-blue-100 border-blue-400 text-blue-700',
                'success' => 'bg-green-100 border-green-400 text-green-700',
                'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
                'error' => 'bg-red-100 border-red-400 text-red-700',
            ];

            $class = $classes[$type] ?? $classes['info'];

            return sprintf(
                '<div class="border-l-4 p-4 mb-4 %s" role="alert">%s</div>',
                e($class),
                $content
            );
        });
    }

    /**
     * Register a shortcode handler.
     *
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function register(string $name, callable $callback): void
    {
        $this->handlers[$name] = $callback;
    }

    /**
     * Parse content and replace shortcodes.
     *
     * @param string $content
     * @return string
     */
    public function parse(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        // Parse self-closing shortcodes: [shortcode attr="value"]
        $content = $this->parseSelfClosing($content);

        // Parse paired shortcodes: [shortcode]content[/shortcode]
        $content = $this->parsePaired($content);

        return $content;
    }

    /**
     * Parse self-closing shortcodes.
     *
     * @param string $content
     * @return string
     */
    protected function parseSelfClosing(string $content): string
    {
        // Pattern: [shortcode attr="value" attr2="value2"]
        $pattern = '/\[(\w+)(.*?)\]/';

        return preg_replace_callback($pattern, function ($matches) {
            $name = $matches[1];
            $attributesString = $matches[2];

            if (!isset($this->handlers[$name])) {
                return $matches[0]; // Return original if no handler
            }

            $attributes = $this->parseAttributes($attributesString);
            return call_user_func($this->handlers[$name], $attributes);
        }, $content);
    }

    /**
     * Parse paired shortcodes with content.
     *
     * @param string $content
     * @return string
     */
    protected function parsePaired(string $content): string
    {
        // Pattern: [shortcode attr="value"]content[/shortcode]
        $pattern = '/\[(\w+)(.*?)\](.*?)\[\/\1\]/s';

        return preg_replace_callback($pattern, function ($matches) {
            $name = $matches[1];
            $attributesString = $matches[2];
            $innerContent = $matches[3];

            if (!isset($this->handlers[$name])) {
                return $matches[0]; // Return original if no handler
            }

            $attributes = $this->parseAttributes($attributesString);
            return call_user_func($this->handlers[$name], $attributes, $innerContent);
        }, $content);
    }

    /**
     * Parse attributes from shortcode string.
     *
     * @param string $attributesString
     * @return array
     */
    protected function parseAttributes(string $attributesString): array
    {
        $attributes = [];
        
        // Pattern: attr="value" or attr='value' or attr=value
        $pattern = '/(\w+)=["\']?([^"\'\s]+)["\']?/';
        
        if (preg_match_all($pattern, $attributesString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[2];
            }
        }

        return $attributes;
    }

    /**
     * Check if content contains any shortcodes.
     *
     * @param string $content
     * @return bool
     */
    public function hasShortcodes(string $content): bool
    {
        return preg_match('/\[\w+.*?\]/', $content) === 1;
    }

    /**
     * Strip all shortcodes from content.
     *
     * @param string $content
     * @return string
     */
    public function strip(string $content): string
    {
        // Remove all shortcodes but keep their content for paired tags
        $content = preg_replace('/\[(\w+).*?\](.*?)\[\/\1\]/s', '$2', $content);
        $content = preg_replace('/\[\w+.*?\]/', '', $content);
        
        return $content;
    }
}
