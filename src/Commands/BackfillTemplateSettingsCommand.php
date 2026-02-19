<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Westlinks\Wlcms\Models\ContentItem;
use Westlinks\Wlcms\Models\ContentTemplateSettings;
use Westlinks\Wlcms\Services\TemplateManager;

class BackfillTemplateSettingsCommand extends Command
{
    protected $signature = 'wlcms:backfill-template-settings
                            {--dry-run : Preview what would be created without making changes}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Create missing template settings records for content items with templates';

    public function handle(): int
    {
        $this->info('🔧 WLCMS Template Settings Backfill Tool');
        $this->info('========================================');

        // Find content items with templates but no settings
        $orphanedItems = ContentItem::whereNotNull('template')
            ->doesntHave('templateSettings')
            ->get();

        if ($orphanedItems->isEmpty()) {
            $this->info('✅ No orphaned template assignments found. All content items have template settings.');
            return self::SUCCESS;
        }

        $this->warn("Found {$orphanedItems->count()} content items with templates but no settings records.");
        
        // Show summary
        $this->table(
            ['Template', 'Count'],
            $orphanedItems->groupBy('template')->map(fn($items, $template) => [
                'template' => $template,
                'count' => $items->count()
            ])->values()->toArray()
        );

        if ($this->option('dry-run')) {
            $this->info("\n📋 DRY RUN - No changes will be made");
            $this->showPreview($orphanedItems->take(5));
            return self::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to create template settings for these items?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        // Process items
        $bar = $this->output->createProgressBar($orphanedItems->count());
        $bar->start();

        $created = 0;
        $errors = 0;

        foreach ($orphanedItems as $item) {
            try {
                $this->createTemplateSettings($item);
                $created++;
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Error processing item #{$item->id} ({$item->title}): {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Created {$created} template settings records");
        if ($errors > 0) {
            $this->warn("⚠️  {$errors} errors occurred");
        }

        return self::SUCCESS;
    }

    protected function createTemplateSettings(ContentItem $item): void
    {
        $templateConfig = TemplateManager::get($item->template);
        
        if (!$templateConfig) {
            throw new \Exception("Template '{$item->template}' not found in registry");
        }

        // Prepare zones data - migrate content to appropriate zone
        $zonesData = $this->prepareZonesData($item, $templateConfig);

        // Prepare default settings
        $settings = $this->prepareDefaultSettings($templateConfig);

        // Create the template settings record
        ContentTemplateSettings::create([
            'content_id' => $item->id,
            'zones_data' => $zonesData,
            'settings' => $settings,
        ]);
    }

    protected function prepareZonesData(ContentItem $item, array $templateConfig): array
    {
        $zonesData = [];
        $zones = $templateConfig['zones'] ?? [];

        // If there's existing content in the content column and template has a 'content' or 'main' zone, migrate it
        if (!empty($item->content)) {
            if (isset($zones['content'])) {
                $zonesData['content'] = $item->content;
            } elseif (isset($zones['main'])) {
                $zonesData['main'] = $item->content;
            } elseif (count($zones) === 1) {
                // If template has only one zone, put content there
                $zoneName = array_key_first($zones);
                $zonesData[$zoneName] = $item->content;
            }
        }

        // Initialize remaining zones with empty values
        foreach ($zones as $zoneName => $zoneConfig) {
            if (!isset($zonesData[$zoneName])) {
                $zoneType = $zoneConfig['type'] ?? 'rich_text';
                $zonesData[$zoneName] = match($zoneType) {
                    'form' => ['form_id' => '', 'embed_code' => '', 'embed_type' => 'built-in'],
                    'media' => null,
                    default => '',
                };
            }
        }

        return $zonesData;
    }

    protected function prepareDefaultSettings(array $templateConfig): array
    {
        $settings = [];
        $settingsSchema = $templateConfig['settings_schema'] ?? [];

        foreach ($settingsSchema as $settingKey => $settingConfig) {
            $settings[$settingKey] = $settingConfig['default'] ?? match($settingConfig['type'] ?? 'text') {
                'checkbox', 'boolean' => false,
                'select', 'radio' => $settingConfig['options'] ? array_key_first($settingConfig['options']) : null,
                'media' => null,
                'color' => '#ffffff',
                default => null,
            };
        }

        return $settings;
    }

    protected function showPreview($items): void
    {
        $this->newLine();
        $this->info("Preview of first {$items->count()} items to be processed:");
        $this->newLine();

        foreach ($items as $item) {
            $this->line("ID: {$item->id} | Title: {$item->title} | Template: {$item->template}");
            $templateConfig = TemplateManager::get($item->template);
            
            if ($templateConfig) {
                $zones = array_keys($templateConfig['zones'] ?? []);
                $this->line("  → Will create zones: " . implode(', ', $zones));
                if (!empty($item->content)) {
                    $contentPreview = substr(strip_tags($item->content), 0, 60);
                    $this->line("  → Existing content will be migrated: \"{$contentPreview}...\"");
                }
            } else {
                $this->warn("  → WARNING: Template not found in registry!");
            }
            $this->newLine();
        }
    }
}
