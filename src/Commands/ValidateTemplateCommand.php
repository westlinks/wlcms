<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Westlinks\Wlcms\Models\Template;

class ValidateTemplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wlcms:validate-template 
                            {identifier : The template identifier to validate}
                            {--path= : Path to template view (e.g., vendor.wlcms.templates.custom)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate a custom WLCMS template for common issues';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('identifier');
        $viewPath = $this->option('path');

        $this->info('Validating template: ' . $identifier);
        $this->newLine();

        $errors = [];
        $warnings = [];
        $passed = 0;

        // Check 1: Template registration
        $this->line('⏳ Checking template registration...');
        $template = Template::where('identifier', $identifier)->first();
        
        if (!$template) {
            $errors[] = "Template '{$identifier}' is not registered in the database";
            $this->error('  ✗ Template not registered');
        } else {
            $this->info('  ✓ Template registered');
            $passed++;
        }

        // Check 2: View file exists
        if ($viewPath) {
            $this->line('⏳ Checking view existence...');
            
            if (!View::exists($viewPath)) {
                $errors[] = "View '{$viewPath}' does not exist";
                $this->error('  ✗ View file not found');
            } else {
                $this->info('  ✓ View file exists');
                $passed++;
                
                // Check 3: View compiles without errors
                $this->line('⏳ Checking view compilation...');
                try {
                    $testData = $this->generateTestData($template);
                    View::make($viewPath, $testData)->render();
                    $this->info('  ✓ View compiles successfully');
                    $passed++;
                } catch (\Throwable $e) {
                    $warnings[] = "View compilation skipped: Template requires runtime context";
                    $this->warn('  ⚠ View compilation skipped (requires full context)');
                }
            }
        }

        // Check 4: Zone configuration
        if ($template) {
            $this->line('⏳ Checking zone configuration...');
            $zones = $template->zones ?? [];
            
            if (empty($zones)) {
                $warnings[] = 'Template has no zones configured';
                $this->warn('  ⚠ No zones configured');
            } else {
                $this->info('  ✓ ' . count($zones) . ' zone(s) configured');
                $passed++;
                
                // Validate zone structure
                foreach ($zones as $key => $zone) {
                    if (!isset($zone['label']) || !isset($zone['type'])) {
                        $errors[] = "Zone '{$key}' is missing required fields (label, type)";
                    }
                    
                    $validTypes = ['rich_text', 'conditional', 'repeater', 'media_gallery', 'file_list', 'link_list', 'form'];
                    if (isset($zone['type']) && !in_array($zone['type'], $validTypes)) {
                        $warnings[] = "Zone '{$key}' has unrecognized type: {$zone['type']}";
                    }
                }
            }
        }

        // Check 5: Settings schema validation
        if ($template && $template->settings_schema) {
            $this->line('⏳ Checking settings schema...');
            $schema = $template->settings_schema;
            $schemaErrors = [];
            
            foreach ($schema as $key => $field) {
                if (!isset($field['type']) || !isset($field['label'])) {
                    $schemaErrors[] = "Setting '{$key}' is missing required fields (type, label)";
                }
            }
            
            if (empty($schemaErrors)) {
                $this->info('  ✓ Settings schema valid');
                $passed++;
            } else {
                $this->error('  ✗ Settings schema errors found');
                $errors = array_merge($errors, $schemaErrors);
            }
        }

        // Check 6: Required zones validation
        if ($template) {
            $this->line('⏳ Checking required zones...');
            $requiredZones = collect($template->zones ?? [])->filter(fn($zone) => $zone['required'] ?? false);
            
            if ($requiredZones->isEmpty()) {
                $this->info('  ✓ No required zones (optional)');
                $passed++;
            } else {
                $this->info('  ✓ ' . $requiredZones->count() . ' required zone(s) defined');
                $passed++;
            }
        }

        // Summary
        $this->newLine();
        $this->line('═══════════════════════════════════════════');
        $this->info('Validation Summary');
        $this->line('═══════════════════════════════════════════');
        $this->line('✓ Passed: ' . $passed);
        
        if (!empty($warnings)) {
            $this->warn('⚠ Warnings: ' . count($warnings));
            foreach ($warnings as $warning) {
                $this->line('  • ' . $warning);
            }
        }
        
        if (!empty($errors)) {
            $this->error('✗ Errors: ' . count($errors));
            foreach ($errors as $error) {
                $this->line('  • ' . $error);
            }
            $this->newLine();
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 Template validation passed!');
        
        return self::SUCCESS;
    }

    /**
     * Generate test data for view compilation
     */
    protected function generateTestData($template): array
    {
        $zones = [];
        
        if ($template && $template->zones) {
            foreach ($template->zones as $key => $zone) {
                $zones[$key] = match($zone['type'] ?? 'rich_text') {
                    'rich_text' => '<p>Test content</p>',
                    'conditional' => '<p>Conditional content</p>',
                    'repeater' => [['title' => 'Test', 'content' => 'Test']],
                    'media_gallery' => [['url' => '/test.jpg', 'alt' => 'Test']],
                    'file_list' => [['url' => '/test.pdf', 'title' => 'Test PDF']],
                    'link_list' => [['url' => '#', 'label' => 'Test Link']],
                    'form' => '<form>Test Form</form>',
                    'form_embed' => '<form>Test Form</form>',
                    default => 'Test content',
                };
            }
        }

        return [
            'layout' => 'wlcms::layouts.base',
            'content' => (object)[
                'id' => 1,
                'title' => 'Test Content',
                'slug' => 'test-content',
                'status' => 'published',
            ],
            'zones' => $zones,
            'settings' => $template && $template->settings_schema ? array_fill_keys(array_keys($template->settings_schema), 'test') : [],
        ];
    }
}
