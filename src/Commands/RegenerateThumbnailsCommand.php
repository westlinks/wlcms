<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Westlinks\Wlcms\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Westlinks\Wlcms\Http\Controllers\Admin\MediaController;

class RegenerateThumbnailsCommand extends Command
{
    protected $signature = 'wlcms:regenerate-thumbnails {--force : Force regeneration even if thumbnails exist}';
    protected $description = 'Regenerate thumbnails for all media assets with improved quality';

    public function handle()
    {
        $this->info('Starting thumbnail regeneration...');
        
        $mediaAssets = MediaAsset::where('type', 'image')->get();
        $this->info("Found {$mediaAssets->count()} image assets to process.");
        
        $progressBar = $this->output->createProgressBar($mediaAssets->count());
        $progressBar->start();
        
        $regenerated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($mediaAssets as $media) {
            try {
                // Check if we should regenerate
                $shouldRegenerate = $this->option('force') || !$media->thumbnails;
                
                if (!$shouldRegenerate) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }
                
                // Check if original file exists
                if (!Storage::disk($media->disk)->exists($media->path)) {
                    $this->newLine();
                    $this->warn("Original file not found for media ID {$media->id}: {$media->path}");
                    $errors++;
                    $progressBar->advance();
                    continue;
                }
                
                // Generate new thumbnails using improved method
                $thumbnails = $this->generateThumbnails($media->disk, $media->path, $media->filename);
                
                if ($thumbnails) {
                    // Update media asset with new thumbnails
                    $media->thumbnails = $thumbnails;
                    $media->save();
                    $regenerated++;
                } else {
                    $errors++;
                }
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error processing media ID {$media->id}: " . $e->getMessage());
                $errors++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("Thumbnail regeneration completed:");
        $this->info("✅ Regenerated: {$regenerated}");
        $this->info("⏭️ Skipped: {$skipped}");
        $this->info("❌ Errors: {$errors}");
        
        if ($errors > 0) {
            $this->warn("Some thumbnails could not be regenerated. Check the logs for details.");
        }
    }
    
    /**
     * Generate thumbnails using the improved algorithm from MediaController
     */
    protected function generateThumbnails(string $disk, string $originalPath, string $filename): ?array
    {
        try {
            // Check if disk is local or remote (S3, etc.)
            $diskDriver = Storage::disk($disk);
            $isLocalDisk = method_exists($diskDriver, 'path');
            
            if ($isLocalDisk) {
                $fullPath = Storage::disk($disk)->path($originalPath);
                $image = \Intervention\Image\Laravel\Facades\Image::read($fullPath);
            } else {
                // For S3/remote: download to temp file first
                $tempFile = tempnam(sys_get_temp_dir(), 'wlcms_thumb_');
                file_put_contents($tempFile, Storage::disk($disk)->get($originalPath));
                $image = \Intervention\Image\Laravel\Facades\Image::read($tempFile);
            }
            
            $thumbnails = [];
            $thumbnailSizes = [
                'thumb' => [150, 150],
                'small' => [300, 300],
                'medium' => [600, 600],
                'large' => [1200, 1200],
            ];

            $directory = dirname($originalPath);
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Get quality settings from config
            $quality = config('wlcms.media.image.quality', 90);

            foreach ($thumbnailSizes as $size => [$width, $height]) {
                $thumbnailFilename = "{$nameWithoutExt}_{$size}.{$extension}";
                $thumbnailPath = "{$directory}/thumbs/{$thumbnailFilename}";
                
                // Use cover() method for better quality and proper aspect ratio handling
                $resized = $image->cover($width, $height);
                
                if ($isLocalDisk) {
                    // Local storage: create directory and save with quality
                    $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);
                    
                    // Ensure directory exists
                    $thumbnailDir = dirname($thumbnailFullPath);
                    if (!is_dir($thumbnailDir)) {
                        mkdir($thumbnailDir, 0755, true);
                    }
                    
                    // Encode with quality for better results
                    $resized = $resized->toJpeg($quality);
                    file_put_contents($thumbnailFullPath, (string) $resized);
                } else {
                    // S3/remote storage: encode with quality then upload
                    $encoded = $resized->toJpeg($quality);
                    Storage::disk($disk)->put($thumbnailPath, (string) $encoded);
                }
                
                $thumbnails[$size] = $thumbnailPath;
            }

            // Clean up temp files for remote storage
            if (!$isLocalDisk && isset($tempFile)) {
                unlink($tempFile);
            }

            return $thumbnails;

        } catch (\Exception $e) {
            $this->error('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }
}