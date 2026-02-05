<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wlcms:install {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Install WLCMS package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing WLCMS...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'wlcms-config',
            '--force' => $this->option('force'),
        ]);

        // Publish assets
        $this->call('vendor:publish', [
            '--tag' => 'wlcms-assets',
            '--force' => $this->option('force'),
        ]);

        // Run migrations
        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate');
        }

        // Create storage directories
        $this->createStorageDirectories();

        // Install and configure frontend assets
        if ($this->confirm('Install and configure frontend assets?', true)) {
            $this->installAssets();
            $this->updateViteConfig();
        }

        $this->info('WLCMS installation completed!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Review the configuration file: config/wlcms.php');
        $this->line('2. Run: npm install && npm run build');
        $this->line('3. Run: php artisan migrate (if you skipped it)');
        $this->line('4. Visit /admin/cms to start using the CMS');

        return Command::SUCCESS;
    }

    /**
     * Create necessary storage directories.
     */
    protected function createStorageDirectories(): void
    {
        $mediaPath = config('wlcms.media.path', 'cms/media');
        $disk = config('wlcms.media.disk', 'public');

        $directories = [
            $mediaPath,
            $mediaPath . '/thumbnails',
        ];

        foreach ($directories as $directory) {
            if (!\Storage::disk($disk)->exists($directory)) {
                \Storage::disk($disk)->makeDirectory($directory);
                $this->info("Created directory: {$directory}");
            }
        }
    }

    /**
     * Install and compile frontend assets.
     */
    protected function installAssets(): void
    {
        $this->info('Updating package.json with WLCMS dependencies...');
        
        $packageJsonPath = base_path('package.json');
        if (file_exists($packageJsonPath)) {
            $packageJson = json_decode(file_get_contents($packageJsonPath), true);
            
            $dependencies = [
                '@tiptap/core' => '^2.0.0',
                '@tiptap/pm' => '^2.0.0',
                '@tiptap/starter-kit' => '^2.0.0',
                '@tiptap/extension-image' => '^2.0.0',
                '@tiptap/extension-link' => '^2.0.0',
            ];

            $added = false;
            foreach ($dependencies as $package => $version) {
                if (!isset($packageJson['dependencies'][$package]) && !isset($packageJson['devDependencies'][$package])) {
                    $packageJson['dependencies'][$package] = $version;
                    $added = true;
                }
            }

            if ($added) {
                file_put_contents($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
                $this->info('✓ Updated package.json with WLCMS dependencies');
            } else {
                $this->info('✓ WLCMS dependencies already in package.json');
            }
        } else {
            $this->warn('package.json not found. Please ensure you have a Laravel Vite setup.');
        }
    }

    /**
     * Update vite.config.js to include WLCMS assets.
     */
    protected function updateViteConfig(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        
        if (!file_exists($viteConfigPath)) {
            $this->warn('vite.config.js not found. Skipping Vite configuration.');
            return;
        }

        $viteConfig = file_get_contents($viteConfigPath);
        
        // Check if WLCMS assets are already included
        if (strpos($viteConfig, 'resources/vendor/wlcms/js/wlcms.js') !== false) {
            $this->info('✓ Vite config already includes WLCMS assets');
            return;
        }

        $this->info('Updating vite.config.js...');

        // Pattern to find the input array
        $pattern = "/(input:\s*\[[\s\S]*?)(\])/";
        
        if (preg_match($pattern, $viteConfig, $matches)) {
            // Add WLCMS assets to the input array
            $originalInput = $matches[1];
            $replacement = $originalInput . ",\n                'resources/vendor/wlcms/js/wlcms.js',\n                'resources/vendor/wlcms/css/wlcms.css'\n            " . $matches[2];
            
            $updatedConfig = preg_replace($pattern, $replacement, $viteConfig, 1);
            
            file_put_contents($viteConfigPath, $updatedConfig);
            $this->info('✓ Updated vite.config.js to include WLCMS assets');
        } else {
            $this->warn('Could not automatically update vite.config.js. Please manually add:');
            $this->line("  'resources/vendor/wlcms/js/wlcms.js',");
            $this->line("  'resources/vendor/wlcms/css/wlcms.css'");
            $this->line('to the input array in your vite.config.js');
        }
    }
}
