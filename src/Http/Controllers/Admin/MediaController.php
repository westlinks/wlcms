<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\MediaAsset;
use Westlinks\Wlcms\Models\MediaFolder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaAsset::with('folder');

        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $media = $query->latest()->paginate(24);
        $folders = MediaFolder::all();

        return view('wlcms::admin.media.index', compact('media', 'folders'));
    }

    public function show(MediaAsset $media)
    {
        // Generate all possible URLs for the media
        $urls = [
            'original' => Storage::disk($media->disk)->url($media->path),
        ];
        
        // Add thumbnail URLs if they exist
        if ($media->thumbnails) {
            foreach ($media->thumbnails as $size => $path) {
                $urls[$size] = Storage::disk($media->disk)->url($path);
            }
        }
        
        // Format file size
        $fileSize = $media->size;
        $sizeFormatted = $this->formatFileSize($fileSize);
        
        // Extract dimensions for images
        $dimensions = null;
        if ($media->type === 'image' && $media->metadata) {
            $width = $media->metadata['width'] ?? null;
            $height = $media->metadata['height'] ?? null;
            if ($width && $height) {
                $dimensions = "{$width} × {$height} px";
            }
        }

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'name' => $media->name,
                'original_name' => $media->original_name,
                'type' => $media->type,
                'mime_type' => $media->mime_type,
                'size' => $fileSize,
                'size_formatted' => $sizeFormatted,
                'dimensions' => $dimensions,
                'alt_text' => $media->alt_text,
                'caption' => $media->caption,
                'description' => $media->description,
                'uploaded_by' => $media->uploaded_by,
                'created_at' => $media->created_at->format('M j, Y g:i A'),
                'updated_at' => $media->updated_at->format('M j, Y g:i A'),
                'urls' => $urls,
                'folder' => $media->folder ? [
                    'id' => $media->folder->id,
                    'name' => $media->folder->name
                ] : null,
                'metadata' => $media->metadata,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'alt_text' => 'nullable|string',
            'caption' => 'nullable|string',
            'description' => 'nullable|string',
            'folder_id' => 'nullable|exists:cms_media_folders,id',
        ]);

        $media = MediaAsset::create($validated);

        return response()->json([
            'media' => $media,
            'message' => 'Media created successfully'
        ], 201);
    }

    public function update(Request $request, MediaAsset $media)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'alt_text' => 'nullable|string',
            'caption' => 'nullable|string',
            'description' => 'nullable|string',
            'folder_id' => 'nullable|exists:cms_media_folders,id',
        ]);

        $media->update($validated);

        return response()->json([
            'media' => $media,
            'message' => 'Media updated successfully'
        ]);
    }

    public function destroy(MediaAsset $media)
    {
        // Delete file from storage
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully'
        ]);
    }

    public function upload(Request $request)
    {
        $maxSize = config('wlcms.media.max_file_size', 20480);
        
        $request->validate([
            'files.*' => "required|file|max:{$maxSize}",
            'folder_id' => 'nullable|exists:cms_media_folders,id',
        ]);

        $uploadedMedia = [];
        $disk = config('wlcms.media.disk', 'public');

        foreach ($request->file('files', []) as $file) {
            try {
                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . str()->random(8) . '.' . $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType();
                
                // Determine file type category
                $type = $this->getFileType($mimeType);
                
                // Create directory path based on type and date
                $directory = "wlcms/{$type}s/" . date('Y/m');
                $path = $file->storeAs($directory, $filename, $disk);

                // Initialize metadata
                $metadata = [];
                $thumbnails = null;

                // Process image files
                if ($type === 'image') {
                    $metadata = $this->processImage($file, $disk, $path);
                    $thumbnails = $this->generateThumbnails($disk, $path, $filename);
                }

                // Create database record
                $mediaAsset = MediaAsset::create([
                    'name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'original_name' => $originalName,
                    'filename' => $filename,
                    'path' => $path,
                    'disk' => $disk,
                    'mime_type' => $mimeType,
                    'type' => $type,
                    'size' => $file->getSize(),
                    'metadata' => $metadata,
                    'folder_id' => $request->folder_id,
                    'thumbnails' => $thumbnails,
                    'uploaded_by' => auth()->user()?->name ?? 'Unknown',
                ]);

                $uploadedMedia[] = [
                    'id' => $mediaAsset->id,
                    'name' => $mediaAsset->name,
                    'original_name' => $mediaAsset->original_name,
                    'type' => $mediaAsset->type,
                    'size' => $mediaAsset->size,
                    'url' => Storage::disk($disk)->url($path),
                    'thumbnail_url' => $thumbnails ? Storage::disk($disk)->url($thumbnails['medium'] ?? $path) : null,
                ];

            } catch (\Exception $e) {
                $uploadedMedia[] = [
                    'name' => $file->getClientOriginalName(),
                    'error' => 'Upload failed: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'uploaded_media' => $uploadedMedia,
            'message' => count($uploadedMedia) . ' file(s) processed successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:cms_media_assets,id'
        ]);

        $deleted = MediaAsset::whereIn('id', $request->media_ids)->delete();

        return response()->json([
            'deleted_count' => $deleted,
            'message' => "Deleted {$deleted} media items successfully"
        ]);
    }

    public function download(MediaAsset $media)
    {
        if (!Storage::disk($media->disk)->exists($media->path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::disk($media->disk)->download($media->path, $media->original_name);
    }

    /**
     * Determine file type category based on MIME type
     */
    protected function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        // Document types
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'application/rtf',
        ];

        if (in_array($mimeType, $documentTypes)) {
            return 'document';
        }

        return 'file'; // Generic file type
    }

    /**
     * Process image metadata using Intervention Image
     */
    protected function processImage($file, string $disk, string $path): array
    {
        try {
            $image = \Intervention\Image\Laravel\Facades\Image::read($file);
            
            return [
                'width' => $image->width(),
                'height' => $image->height(),
                'aspect_ratio' => round($image->width() / $image->height(), 2),
                'color_space' => $image->colorspace(),
                'exif' => $this->extractSafeExifData($file),
            ];
        } catch (\Exception $e) {
            return [
                'processing_error' => 'Could not process image metadata: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate multiple thumbnail sizes for images
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

            foreach ($thumbnailSizes as $size => [$width, $height]) {
                $thumbnailFilename = "{$nameWithoutExt}_{$size}.{$extension}";
                $thumbnailPath = "{$directory}/thumbs/{$thumbnailFilename}";
                
                // Resize image maintaining aspect ratio
                $resized = $image->scale($width, $height);
                
                if ($isLocalDisk) {
                    // Local storage: create directory and save directly
                    $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);
                    
                    // Ensure directory exists
                    $thumbnailDir = dirname($thumbnailFullPath);
                    if (!is_dir($thumbnailDir)) {
                        mkdir($thumbnailDir, 0755, true);
                    }
                    
                    $resized->save($thumbnailFullPath);
                } else {
                    // S3/remote storage: save to temp file then upload
                    $tempThumbFile = tempnam(sys_get_temp_dir(), 'wlcms_thumb_' . $size . '_');
                    $resized->save($tempThumbFile, quality: config('wlcms.media.image.quality', 85));
                    
                    // Upload to storage disk
                    Storage::disk($disk)->put($thumbnailPath, file_get_contents($tempThumbFile));
                    
                    // Clean up temp file
                    unlink($tempThumbFile);
                }
                
                $thumbnails[$size] = $thumbnailPath;
            }

            // Clean up temp files for remote storage
            if (!$isLocalDisk && isset($tempFile)) {
                unlink($tempFile);
            }

            return $thumbnails;

        } catch (\Exception $e) {
            // Log error but don't fail upload completely
            Log::error('WLCMS Thumbnail generation failed: ' . $e->getMessage(), [
                'disk' => $disk,
                'original_path' => $originalPath,
                'filename' => $filename
            ]);
            return null;
        }
    }

    /**
     * Extract safe EXIF data from image
     */
    protected function extractSafeExifData($file): array
    {
        try {
            $exifData = @exif_read_data($file->getPathname());
            
            if (!$exifData) {
                return [];
            }

            // Extract only safe, useful EXIF data
            return array_filter([
                'camera_make' => $exifData['Make'] ?? null,
                'camera_model' => $exifData['Model'] ?? null,
                'date_taken' => $exifData['DateTime'] ?? null,
                'orientation' => $exifData['Orientation'] ?? null,
                'iso' => $exifData['ISOSpeedRatings'] ?? null,
                'focal_length' => $exifData['FocalLength'] ?? null,
                'aperture' => $exifData['COMPUTED']['ApertureFNumber'] ?? null,
                'exposure_time' => $exifData['ExposureTime'] ?? null,
            ]);

        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Format file size in human readable format
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 1) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
}