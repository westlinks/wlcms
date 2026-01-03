<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\MediaAsset;
use Westlinks\Wlcms\Models\MediaFolder;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'files.*' => 'required|file|max:10240', // 10MB max
        ]);

        $uploadedMedia = [];

        foreach ($request->file('files', []) as $file) {
            // This would contain actual file upload logic
            // For now, return a placeholder response
            $uploadedMedia[] = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'message' => 'Upload feature not yet implemented'
            ];
        }

        return response()->json([
            'uploaded_media' => $uploadedMedia,
            'message' => 'Files processed (upload feature pending implementation)'
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
}