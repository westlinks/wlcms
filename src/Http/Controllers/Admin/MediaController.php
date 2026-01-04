<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\MediaAsset;
use Westlinks\Wlcms\Models\MediaFolder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

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
        return response()->json([
            'media' => $media->load('folder'),
            'message' => 'Media retrieved successfully'
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
            $fullPath = Storage::disk($disk)->path($originalPath);
            $image = \Intervention\Image\Laravel\Facades\Image::read($fullPath);
            
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
                
                // Save thumbnail
                $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);
                
                // Ensure directory exists
                $thumbnailDir = dirname($thumbnailFullPath);
                if (!is_dir($thumbnailDir)) {
                    mkdir($thumbnailDir, 0755, true);
                }
                
                $resized->save($thumbnailFullPath);
                $thumbnails[$size] = $thumbnailPath;
            }

            return $thumbnails;

        } catch (\Exception $e) {
            // Return null if thumbnail generation fails, original image still works
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
}