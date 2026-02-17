<?php

namespace Westlinks\Wlcms\Services;

use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\Template;
use Westlinks\Wlcms\Models\ContentTemplateSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\View\View as ViewContract;

class TemplateRenderer
{
    /**
     * Zone processor for handling different zone types.
     *
     * @var ZoneProcessor
     */
    protected ZoneProcessor $zoneProcessor;

    /**
     * Constructor.
     */
    public function __construct(ZoneProcessor $zoneProcessor)
    {
        $this->zoneProcessor = $zoneProcessor;
    }

    /**
     * Render a content item with its template.
     *
     * @param ContentItem $contentItem Content item to render
     * @param array $additionalData Additional data to pass to view
     * @return ViewContract
     * @throws \Exception
     */
    public function render(ContentItem $contentItem, array $additionalData = []): ViewContract
    {
        // Get template configuration
        $template = $contentItem->templateConfig;

        if (!$template) {
            // Fallback to default template
            $template = TemplateManager::get($contentItem->template) 
                ?? TemplateManager::get('full-width');

            if (!$template) {
                throw new \Exception("Template '{$contentItem->template}' not found and no fallback available.");
            }
        }

        // Get template settings
        $templateSettings = $contentItem->templateSettings;
        $settings = $templateSettings ? $templateSettings->getAllSettings() : [];
        $zonesData = $templateSettings ? $templateSettings->getAllZonesData() : [];

        // Process zones data
        $processedZones = $this->processZones($template, $zonesData);

        // Prepare view data
        $viewData = array_merge([
            'contentItem' => $contentItem,
            'content' => $contentItem,  // Alias for backward compatibility
            'template' => $template,
            'settings' => $settings,
            'zones' => $processedZones,
            'meta' => $contentItem->meta ?? [],
            'layout' => $additionalData['layout'] ?? 'wlcms::layouts.base',
        ], $additionalData);

        // Render the template view
        return View::make($template->view_path, $viewData);
    }

    /**
     * Process all zones data based on template configuration.
     *
     * @param Template $template Template instance
     * @param array $zonesData Raw zones data
     * @return array Processed zones data
     */
    protected function processZones(Template $template, array $zonesData): array
    {
        $processed = [];
        $zones = $template->zones ?? [];

        foreach ($zones as $zoneId => $zoneConfig) {
            $data = $zonesData[$zoneId] ?? null;

            if ($data !== null) {
                $processed[$zoneId] = $this->zoneProcessor->process(
                    $zoneConfig['type'] ?? 'rich_text',
                    $data,
                    $zoneConfig
                );
            } else {
                $processed[$zoneId] = null;
            }
        }

        return $processed;
    }

    /**
     * Render a specific zone.
     *
     * @param string $zoneType Zone type (rich_text, repeater, etc.)
     * @param mixed $data Zone data
     * @param array $config Zone configuration
     * @return string Rendered zone HTML
     */
    public function renderZone(string $zoneType, mixed $data, array $config = []): string
    {
        return $this->zoneProcessor->render($zoneType, $data, $config);
    }

    /**
     * Check if a template view exists.
     *
     * @param string $viewPath View path
     * @return bool
     */
    public function viewExists(string $viewPath): bool
    {
        return View::exists($viewPath);
    }

    /**
     * Get processed template data for preview.
     *
     * @param Template $template Template instance
     * @param array $settings Template settings
     * @param array $zonesData Zones data
     * @return array
     */
    public function getPreviewData(Template $template, array $settings = [], array $zonesData = []): array
    {
        $processedZones = $this->processZones($template, $zonesData);

        return [
            'template' => $template,
            'settings' => $settings,
            'zones' => $processedZones,
            'meta' => [],
        ];
    }

    /**
     * Render template for preview.
     *
     * @param Template $template Template instance
     * @param array $settings Template settings
     * @param array $zonesData Zones data
     * @return ViewContract
     */
    public function renderPreview(Template $template, array $settings = [], array $zonesData = []): ViewContract
    {
        $viewData = $this->getPreviewData($template, $settings, $zonesData);

        return View::make($template->view_path, $viewData);
    }
}
