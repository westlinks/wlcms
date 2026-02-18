<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wlcms:publish-templates 
                            {--force : Overwrite existing published templates}
                            {--template= : Publish only a specific template}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish WLCMS template views to resources/views/vendor/wlcms/templates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $packagePath = dirname(__DIR__, 2) . '/resources/views/templates';
        $publishPath = resource_path('views/vendor/wlcms/templates');
        
        // Check if package templates directory exists
        if (!File::exists($packagePath)) {
            $this->error('Package templates directory not found at: ' . $packagePath);
            return self::FAILURE;
        }

        // Create publish directory if it doesn't exist
        if (!File::exists($publishPath)) {
            File::makeDirectory($publishPath, 0755, true);
            $this->info('Created directory: ' . $publishPath);
        }

        $specificTemplate = $this->option('template');
        $force = $this->option('force');

        if ($specificTemplate) {
            return $this->publishSpecificTemplate($packagePath, $publishPath, $specificTemplate, $force);
        }

        return $this->publishAllTemplates($packagePath, $publishPath, $force);
    }

    /**
     * Publish all templates
     */
    protected function publishAllTemplates(string $packagePath, string $publishPath, bool $force): int
    {
        $templates = File::files($packagePath);
        $published = 0;
        $skipped = 0;

        $this->info('Publishing templates from: ' . $packagePath);
        $this->newLine();

        foreach ($templates as $template) {
            $filename = $template->getFilename();
            $destination = $publishPath . '/' . $filename;

            if (File::exists($destination) && !$force) {
                $this->warn('  ⚠ Skipped (already exists): ' . $filename);
                $skipped++;
                continue;
            }

            File::copy($template->getPathname(), $destination);
            $this->info('  ✓ Published: ' . $filename);
            $published++;
        }

        $this->newLine();
        $this->info("Published {$published} template(s) to: {$publishPath}");
        
        if ($skipped > 0) {
            $this->warn("Skipped {$skipped} existing template(s). Use --force to overwrite.");
        }

        $this->newLine();
        $this->line('You can now customize the templates in:');
        $this->line('  ' . str_replace(base_path(), '', $publishPath));

        return self::SUCCESS;
    }

    /**
     * Publish a specific template
     */
    protected function publishSpecificTemplate(string $packagePath, string $publishPath, string $template, bool $force): int
    {
        $filename = $template . '.blade.php';
        $source = $packagePath . '/' . $filename;
        $destination = $publishPath . '/' . $filename;

        if (!File::exists($source)) {
            $this->error('Template not found: ' . $template);
            $this->line('Available templates:');
            
            foreach (File::files($packagePath) as $file) {
                $name = str_replace('.blade.php', '', $file->getFilename());
                $this->line('  - ' . $name);
            }
            
            return self::FAILURE;
        }

        if (File::exists($destination) && !$force) {
            $this->warn('Template already exists: ' . $filename);
            $this->line('Use --force to overwrite.');
            return self::FAILURE;
        }

        File::copy($source, $destination);
        $this->info('✓ Published template: ' . $filename);
        $this->line('Location: ' . str_replace(base_path(), '', $destination));

        return self::SUCCESS;
    }
}
