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

        // Publish views
        $this->call('vendor:publish', [
            '--tag' => 'wlcms-views',
            '--force' => $this->option('force'),
        ]);

        // Run migrations
        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate');
        }

        // Create storage directories
        $this->createStorageDirectories();

        // Publish assets if needed
        if ($this->confirm('Install and compile frontend assets?', true)) {
            $this->installAssets();
        }

        $this->info('WLCMS installation completed!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Review the configuration file: config/wlcms.php');
        $this->line('2. Run: php artisan wlcms:permissions (if using Spatie permissions)');
        $this->line('3. Visit /admin/cms to start using the CMS');

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
        $this->info('Installing frontend dependencies...');
        
        // Add Tiptap dependencies to package.json
        $packageJsonPath = base_path('package.json');
        if (file_exists($packageJsonPath)) {
            $packageJson = json_decode(file_get_contents($packageJsonPath), true);
            
            $dependencies = [
                '@tiptap/core' => '^2.0.0',
                '@tiptap/pm' => '^2.0.0',
                '@tiptap/starter-kit' => '^2.0.0',
                '@tiptap/extension-image' => '^2.0.0',
                '@tiptap/extension-link' => '^2.0.0',
                'wlcms' => '^2.0.0',
            ];

            foreach ($dependencies as $package => $version) {
                if (!isset($packageJson['devDependencies'][$package])) {
                    $packageJson['devDependencies'][$package] = $version;
                }
            }

            file_put_contents($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated package.json with WLCMS dependencies');
        }

        // Suggest running npm install
        $this->warn('Remember to run: npm install && npm run build');
    }
}