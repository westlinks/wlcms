<?php
/**
 * Direct thumbnail regeneration script
 * Run this to regenerate all thumbnails with improved quality
 */

// Try to determine Laravel app root
$possibleRoots = [
    __DIR__ . '/../wlprod/public_html',  // Based on structure seen earlier
    __DIR__ . '/..',
    getcwd(),
];

$laravelRoot = null;
foreach ($possibleRoots as $root) {
    if (file_exists($root . '/artisan') && file_exists($root . '/vendor/autoload.php')) {
        $laravelRoot = realpath($root);
        break;
    }
}

if (!$laravelRoot) {
    echo "❌ Could not find Laravel application root.\n";
    echo "Please run this script from your Laravel app directory or ensure the path is correct.\n";
    exit(1);
}

echo "🔍 Found Laravel app at: {$laravelRoot}\n";

// Load Laravel
require_once $laravelRoot . '/vendor/autoload.php';
$app = require_once $laravelRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Now we have Laravel loaded, let's regenerate thumbnails
echo "🚀 Starting thumbnail regeneration...\n\n";

use Westlinks\Wlcms\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;

// Check if WLCMS is available
if (!class_exists('Westlinks\Wlcms\Models\MediaAsset')) {
    echo "❌ WLCMS package not found. Please install it first:\n";
    echo "   composer require westlinks/wlcms\n";
    echo "   php artisan vendor:publish --provider=\"Westlinks\\Wlcms\\WlcmsServiceProvider\"\n";
    exit(1);
}

try {
    $mediaAssets = MediaAsset::where('type', 'image')->get();
    echo "📊 Found {$mediaAssets->count()} image assets to process.\n\n";
    
    if ($mediaAssets->count() === 0) {
        echo "ℹ️  No image assets found. Upload some images first.\n";
        exit(0);
    }
    
    $regenerated = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($mediaAssets as $media) {
        echo "🔧 Processing: {$media->name} (ID: {$media->id})... ";
        
        try {
            // Check if original file exists
            if (!Storage::disk($media->disk)->exists($media->path)) {
                echo "❌ Original file not found\n";
                $errors++;
                continue;
            }
            
            // Generate new thumbnails using improved method
            $thumbnails = generateImprovedThumbnails($media->disk, $media->path, $media->filename);
            
            if ($thumbnails) {
                // Update media asset with new thumbnails
                $media->thumbnails = $thumbnails;
                $media->save();
                echo "✅ Regenerated\n";
                $regenerated++;
            } else {
                echo "❌ Failed\n";
                $errors++;
            }
            
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n📈 Thumbnail regeneration completed:\n";
    echo "   ✅ Regenerated: {$regenerated}\n";
    echo "   ⏭️ Skipped: {$skipped}\n";
    echo "   ❌ Errors: {$errors}\n\n";
    
    if ($errors > 0) {
        echo "⚠️  Some thumbnails could not be regenerated.\n";
    } else {
        echo "🎉 All thumbnails regenerated successfully!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Generate thumbnails using the improved algorithm
 */
function generateImprovedThumbnails(string $disk, string $originalPath, string $filename): ?array
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
        
        // Get quality settings from config (90% default)
        $quality = 90;
        try {
            $quality = config('wlcms.media.image.quality', 90);
        } catch (\Exception $e) {
            // Fallback if config not available
        }

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
                $encoded = $resized->toJpeg($quality);
                file_put_contents($thumbnailFullPath, (string) $encoded);
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
        echo "Generation failed: " . $e->getMessage() . "\n";
        return null;
    }
}