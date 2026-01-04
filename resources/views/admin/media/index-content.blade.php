{{-- Content-only version for embedding in host applications --}}
<div class="space-y-6">
    {{-- Header with title and upload action --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Media Library</h1>
            <p class="text-sm text-gray-600 mt-1">Upload and organize your media files</p>
        </div>
        <div class="flex space-x-3">
            <button type="button" 
                    onclick="triggerFileUpload()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                📁 Upload Files
            </button>
            <input type="file" id="file-upload" multiple accept="image/*,video/*,.pdf,.doc,.docx" class="hidden">
        </div>
    </div>

    {{-- Filter Navigation - Always visible --}}
    <div class="border-b border-gray-200">
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

    {{-- Media Grid or Empty State --}}
    @if($media->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($media as $item)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer"
                     onclick="openMediaModal({{ $item->id }})">
                    <div class="aspect-square bg-gray-100 rounded-t-lg flex items-center justify-center overflow-hidden">
                        @if($item->type === 'image')
                            <img src="{{ $item->getThumbnailUrl('medium') ?: $item->url }}" 
                                 alt="{{ $item->name }}" 
                                 class="w-full h-full object-cover">
                        @elseif($item->type === 'video')
                            <div class="text-center text-gray-500">
                                <span class="text-4xl block">🎥</span>
                                <span class="text-xs">Video</span>
                            </div>
                        @elseif($item->type === 'document')
                            <div class="text-center text-gray-500">
                                <span class="text-4xl block">📄</span>
                                <span class="text-xs">Document</span>
                            </div>
                        @else
                            <div class="text-center text-gray-500">
                                <span class="text-4xl block">📁</span>
                                <span class="text-xs">File</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-3">
                        <h4 class="font-medium text-gray-900 text-sm truncate">{{ $item->name }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ ucfirst($item->type) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($media->hasPages())
            <div class="flex justify-center mt-8">
                {{ $media->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="bg-white rounded-lg shadow p-8 text-center">
            @if(request('type'))
                @switch(request('type'))
                    @case('image')
                        <span class="text-6xl">🖼️</span>
                        <h3 class="text-lg font-semibold text-gray-900 mt-4">No images found</h3>
                        <p class="text-gray-600 mt-2">Upload some images to get started.</p>
                        @break
                    @case('video')
                        <span class="text-6xl">🎥</span>
                        <h3 class="text-lg font-semibold text-gray-900 mt-4">No videos found</h3>
                        <p class="text-gray-600 mt-2">Upload some videos to get started.</p>
                        @break
                    @case('document')
                        <span class="text-6xl">📄</span>
                        <h3 class="text-lg font-semibold text-gray-900 mt-4">No documents found</h3>
                        <p class="text-gray-600 mt-2">Upload some documents to get started.</p>
                        @break
                @endswitch
            @else
                <span class="text-6xl">📁</span>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">No media files yet</h3>
                <p class="text-gray-600 mt-2">Upload your first media files to get started.</p>
            @endif
            
            <button type="button" 
                    onclick="triggerFileUpload()"
                    class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                Upload Files
            </button>
        </div>
    @endif
</div>

{{-- Include all JavaScript and modal functionality from original --}}
@include('wlcms::admin.media.partials.upload-script')
@include('wlcms::admin.media.partials.modal')