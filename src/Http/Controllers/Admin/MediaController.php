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
        // Get current folder
        $currentFolder = null;
        if ($request->filled('folder')) {
            $currentFolder = MediaFolder::find($request->folder);
        }

        // Build folder path for breadcrumbs
        $folderPath = collect();
        if ($currentFolder) {
            $folder = $currentFolder;
            while ($folder) {
                $folderPath->prepend($folder);
                $folder = $folder->parent;
            }
        }

        // Get folders in current location
        $folders = MediaFolder::withCount('files')
            ->where('parent_id', $currentFolder->id ?? null)
            ->orderBy('name')
            ->get();

        // Get media files
        $query = MediaAsset::with('folder');

        // Filter by current folder
        if ($currentFolder) {
            $query->where('folder_id', $currentFolder->id);
        } else {
            $query->whereNull('folder_id'); // Root folder
        }

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $media = $query->latest('created_at')->paginate(24);

        return view('wlcms::admin.media.index', compact('media', 'folders', 'currentFolder', 'folderPath'));
    }

    public function show(MediaAsset $media)
    {
        // Validate that the file path exists
        if (!$media->path) {
            return response()->json([
                'success' => false,
                'error' => 'Media file path not found'
            ], 404);
        }
        
        // Extract dimensions for images
        $dimensions = null;
        if ($media->type === 'image' && $media->metadata) {
            $width = $media->metadata['width'] ?? null;
            $height = $media->metadata['height'] ?? null;
            if ($width && $height) {
                $dimensions = "{$width} × {$height} px";
            }
        }

        // Generate multiple size URLs for images
        $downloadSizes = [];
        $primaryUrl = Storage::disk($media->disk)->url($media->path);
        
        if ($media->type === 'image') {
            $downloadSizes = [
                'original' => [
                    'label' => 'Original',
                    'url' => $primaryUrl,
                    'dimensions' => $dimensions,
                    'description' => 'Full resolution'
                ],
                'large' => [
                    'label' => 'Large',
                    'url' => $primaryUrl, // TODO: Generate resized versions
                    'dimensions' => $dimensions,
                    'description' => '1200px max width'
                ],
                'medium' => [
                    'label' => 'Medium', 
                    'url' => $primaryUrl, // TODO: Generate resized versions
                    'dimensions' => $dimensions,
                    'description' => '600px max width'
                ]
            ];
        }

        return response()->json([
            'id' => $media->id,
            'name' => $media->name,
            'original_name' => $media->original_name,
            'type' => $media->type,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'human_size' => $this->formatFileSize($media->size),
            'dimensions' => $dimensions,
            'alt_text' => $media->alt_text,
            'caption' => $media->caption,
            'description' => $media->description,
            'uploaded_by' => $media->uploaded_by,
            'created_at' => $media->created_at->format('M j, Y g:i A'),
            'updated_at' => $media->updated_at->format('M j, Y g:i A'),
            'url' => $primaryUrl,
            'download_sizes' => $downloadSizes,
            'folder' => $media->folder ? [
                'id' => $media->folder->id,
                'name' => $media->folder->name
            ] : null,
            'metadata' => $media->metadata,
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
            'files' => 'required|array|min:1',
            'files.*' => "required|file|max:{$maxSize}",
            'folder_id' => 'nullable|exists:cms_media_folders,id',
        ]);

        $uploadedMedia = [];
        $errors = [];
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
                $errors[] = [
                    'name' => $file->getClientOriginalName(),
                    'error' => 'Upload failed: ' . $e->getMessage()
                ];
            }
        }

        $successCount = count($uploadedMedia);
        $errorCount = count($errors);
        
        return response()->json([
            'success' => $errorCount === 0,
            'uploaded_media' => $uploadedMedia,
            'errors' => $errors,
            'message' => $successCount > 0 
                ? "{$successCount} file(s) uploaded successfully" . ($errorCount > 0 ? ", {$errorCount} failed" : "")
                : "Upload failed for all files"
        ], $successCount > 0 ? 200 : 422);
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
            
            // Get quality settings from config
            $quality = config('wlcms.media.image.quality', 90); // Increased default quality

            foreach ($thumbnailSizes as $size => [$width, $height]) {
                $thumbnailFilename = "{$nameWithoutExt}_{$size}.{$extension}";
                $thumbnailPath = "{$directory}/thumbs/{$thumbnailFilename}";
                
                // Use fit() method for better quality and proper aspect ratio handling
                // This prevents pixelation and ensures sharp thumbnails
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
     * Serve media files through Laravel instead of direct storage URLs
     */
    public function serve(MediaAsset $media, string $size = 'original')
    {
        try {
            // Determine which file path to serve
            $filePath = null;
            
            if ($size === 'original') {
                $filePath = $media->path;
            } elseif ($media->thumbnails && isset($media->thumbnails[$size])) {
                $filePath = $media->thumbnails[$size];
            } else {
                // If requested size doesn't exist, fallback to original
                $filePath = $media->path;
            }
            
            if (!$filePath) {
                abort(404, 'Media file not found');
            }
            
            // Check if file exists on disk
            if (!Storage::disk($media->disk)->exists($filePath)) {
                abort(404, 'Media file not found on disk');
            }
            
            // Get file contents and metadata
            $fileContents = Storage::disk($media->disk)->get($filePath);
            $mimeType = Storage::disk($media->disk)->mimeType($filePath);
            $lastModified = Storage::disk($media->disk)->lastModified($filePath);
            
            return response($fileContents)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=31536000') // Cache for 1 year
                ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT')
                ->header('Etag', md5($fileContents));
                
        } catch (\Exception $e) {
            \Log::error('Failed to serve media file', [
                'media_id' => $media->id,
                'size' => $size,
                'disk' => $media->disk,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Failed to serve media file');
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

    /**
     * Create a new folder
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:cms_media_folders,id'
        ]);

        MediaFolder::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'parent_id' => $request->parent_id
        ]);

        return back()->with('success', 'Folder created successfully.');
    }

    /**
     * Update a folder
     */
    public function updateFolder(Request $request, MediaFolder $folder)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder->update([
            'name' => $request->name,
            'slug' => \Str::slug($request->name)
        ]);

        return back()->with('success', 'Folder updated successfully.');
    }

    /**
     * Delete a folder
     */
    public function deleteFolder(MediaFolder $folder)
    {
        // Check if folder has any files or subfolders
        if ($folder->files()->exists() || $folder->children()->exists()) {
            return back()->with('error', 'Cannot delete folder that contains files or subfolders.');
        }

        $folder->delete();
        return back()->with('success', 'Folder deleted successfully.');
    }
}