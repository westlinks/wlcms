<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Westlinks\Wlcms\Models\ContentItem;
use App\Models\Article;
use App\Models\Media;

class MigrateContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wlcms:migrate-content 
                           {--verify : Verify migration without making changes}
                           {--force : Force migration without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing articles and media to WLCMS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('verify')) {
            return $this->verifyMigration();
        }

        if (!$this->option('force') && !$this->confirm('This will migrate existing articles and media to WLCMS. Continue?')) {
            return Command::FAILURE;
        }

        $this->info('Starting content migration...');

        $articleCount = $this->migrateArticles();
        $mediaCount = $this->migrateMedia();

        $this->info("Migration completed!");
        $this->line("Migrated {$articleCount} articles and {$mediaCount} media files.");

        return Command::SUCCESS;
    }

    /**
     * Verify the migration without making changes.
     */
    protected function verifyMigration(): int
    {
        $this->info('Verifying migration...');

        $articlesToMigrate = Article::count();
        $mediaToMigrate = Media::count();
        $existingContent = ContentItem::count();

        $this->table(['Type', 'Count'], [
            ['Articles to migrate', $articlesToMigrate],
            ['Media to migrate', $mediaToMigrate],
            ['Existing CMS content', $existingContent],
        ]);

        // Check for potential conflicts
        $conflicts = Article::whereIn('slug', ContentItem::pluck('slug'))->count();
        if ($conflicts > 0) {
            $this->warn("Found {$conflicts} potential slug conflicts");
        }

        $this->info('Verification completed. Run without --verify to perform migration.');

        return Command::SUCCESS;
    }

    /**
     * Migrate articles to content items.
     */
    protected function migrateArticles(): int
    {
        $articles = Article::with(['creator', 'updater'])->get();
        $count = 0;

        $bar = $this->output->createProgressBar($articles->count());
        $bar->start();

        foreach ($articles as $article) {
            // Skip if already migrated
            if (ContentItem::where('slug', $article->slug)->exists()) {
                $bar->advance();
                continue;
            }

            ContentItem::create([
                'title' => $article->title,
                'slug' => $article->slug,
                'subtitle' => $article->subtitle,
                'excerpt' => $article->intro ?? $article->abstract,
                'content' => $article->description,
                'featured_image' => $article->image,
                'template' => $this->mapTemplate($article->template_id),
                'parent_id' => $this->mapParentId($article->parent_id),
                'sort' => $article->sort ?? 0,
                'published' => $article->published ?? true,
                'published_at' => $article->created_at,
                'created_by' => $article->created_by ?? 1,
                'updated_by' => $article->updated_by ?? 1,
                'created_at' => $article->created_at,
                'updated_at' => $article->updated_at,
            ]);

            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        return $count;
    }

    /**
     * Migrate media files to media assets.
     */
    protected function migrateMedia(): int
    {
        if (!class_exists(Media::class)) {
            $this->warn('Media model not found. Skipping media migration.');
            return 0;
        }

        $mediaItems = Media::all();
        $count = 0;

        $bar = $this->output->createProgressBar($mediaItems->count());
        $bar->start();

        foreach ($mediaItems as $media) {
            // Skip if already migrated
            if (\Westlinks\Wlcms\Models\MediaAsset::where('filename', $media->filename)->exists()) {
                $bar->advance();
                continue;
            }

            \Westlinks\Wlcms\Models\MediaAsset::create([
                'filename' => $media->filename,
                'original_filename' => $media->filename,
                'path' => $media->path,
                'disk' => 'public',
                'mime_type' => $this->guessMimeType($media->filename),
                'filesize' => $this->getFileSize($media->path),
                'description' => $media->description,
                'uploaded_by' => 1,
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ]);

            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        return $count;
    }

    /**
     * Map old template ID to new template name.
     */
    protected function mapTemplate(?int $templateId): string
    {
        return match ($templateId) {
            1 => 'default',
            2 => 'full-width',
            3 => 'narrow-right',
            default => 'default',
        };
    }

    /**
     * Map old parent ID to new parent ID.
     */
    protected function mapParentId(?int $oldParentId): ?int
    {
        if (!$oldParentId) {
            return null;
        }

        $oldParent = Article::find($oldParentId);
        if (!$oldParent) {
            return null;
        }

        $newParent = ContentItem::where('slug', $oldParent->slug)->first();
        return $newParent?->id;
    }

    /**
     * Guess MIME type from filename.
     */
    protected function guessMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            default => 'application/octet-stream',
        };
    }

    /**
     * Get file size from storage.
     */
    protected function getFileSize(string $path): int
    {
        try {
            return \Storage::disk('public')->size($path) ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}