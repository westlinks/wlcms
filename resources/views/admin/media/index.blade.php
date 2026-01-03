@extends('wlcms::admin.layout')

@section('title', 'Media Library - WLCMS Admin')
@section('page-title', 'Media Library')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Media Files</h3>
    <div class="flex space-x-3">
        <button type="button" 
                onclick="document.getElementById('file-upload').click()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            📁 Upload Files
        </button>
        <input type="file" id="file-upload" multiple accept="image/*,video/*,.pdf,.doc,.docx" class="hidden">
    </div>
</div>

@if($media->count() > 0)
    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('wlcms.admin.media.index') }}" 
               class="py-2 px-1 border-b-2 {{ !request('type') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                All Files
            </a>
            <a href="{{ route('wlcms.admin.media.index', ['type' => 'image']) }}" 
               class="py-2 px-1 border-b-2 {{ request('type') === 'image' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                🖼️ Images
            </a>
            <a href="{{ route('wlcms.admin.media.index', ['type' => 'document']) }}" 
               class="py-2 px-1 border-b-2 {{ request('type') === 'document' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                📄 Documents
            </a>
            <a href="{{ route('wlcms.admin.media.index', ['type' => 'video']) }}" 
               class="py-2 px-1 border-b-2 {{ request('type') === 'video' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} font-medium text-sm">
                🎥 Videos
            </a>
        </nav>
    </div>

    <!-- Media Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($media as $item)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer"
                 onclick="openMediaModal({{ $item->id }})">
                <div class="aspect-square bg-gray-100 rounded-t-lg flex items-center justify-center overflow-hidden">
                    @if($item->type === 'image')
                        <img src="{{ Storage::disk($item->disk)->url($item->path) }}" 
                             alt="{{ $item->alt_text }}"
                             class="w-full h-full object-cover">
                    @elseif($item->type === 'document')
                        <span class="text-4xl">📄</span>
                    @elseif($item->type === 'video')
                        <span class="text-4xl">🎥</span>
                    @else
                        <span class="text-4xl">📁</span>
                    @endif
                </div>
                <div class="p-3">
                    <h4 class="font-medium text-gray-900 text-sm truncate">{{ $item->name }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($item->type) }} • {{ number_format($item->size / 1024, 1) }}KB</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $media->appends(request()->query())->links() }}
    </div>
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <span class="text-6xl">📁</span>
        <h3 class="text-lg font-semibold text-gray-900 mt-4">No media files yet</h3>
        <p class="text-gray-600 mt-2">Upload your first files to get started with the media library.</p>
        <button type="button" 
                onclick="document.getElementById('file-upload').click()"
                class="inline-block mt-6 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            📁 Upload Files
        </button>
    </div>
@endif

<!-- Media Modal (placeholder) -->
<div id="media-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg max-w-lg w-full mx-4 p-6">
        <h3 class="text-lg font-semibold mb-4">Media Details</h3>
        <p class="text-gray-600">Media modal functionality coming soon!</p>
        <div class="mt-6 flex justify-end">
            <button type="button" 
                    onclick="closeMediaModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openMediaModal(id) {
    // Placeholder - will be enhanced with actual media details
    document.getElementById('media-modal').classList.remove('hidden');
}

function closeMediaModal() {
    document.getElementById('media-modal').classList.add('hidden');
}

// File upload handler (placeholder)
document.getElementById('file-upload').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        alert('File upload functionality coming soon! Selected ' + e.target.files.length + ' files.');
    }
});
</script>
@endsection