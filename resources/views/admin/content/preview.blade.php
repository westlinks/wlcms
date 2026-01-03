@extends('wlcms::admin.layout')

@section('title', 'Preview: ' . $content->title . ' - WLCMS Admin')
@section('page-title', 'Content Preview')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Previewing Content</h3>
            <p class="text-gray-600">{{ ucfirst($content->type) }} • {{ ucfirst($content->status) }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('wlcms.admin.content.edit', $content) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                ✏️ Edit Content
            </a>
            <a href="{{ route('wlcms.admin.content.show', $content) }}" 
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                ← Back to Details
            </a>
        </div>
    </div>

    <!-- Preview Notice -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <span class="text-2xl mr-3">👁️</span>
            <div>
                <h4 class="font-medium text-yellow-800">Content Preview</h4>
                <p class="text-sm text-yellow-700">This is how your content will appear to visitors. Any changes made in the editor will be reflected here.</p>
            </div>
        </div>
    </div>

    <!-- Content Preview -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-8">
            <div class="max-w-5xl">
                <h1 class="text-4xl font-bold mb-4">{{ $content->title }}</h1>
                
                @if($content->excerpt)
                    <p class="text-xl text-blue-100 leading-relaxed">{{ $content->excerpt }}</p>
                @endif
                
                <div class="flex items-center text-blue-100 text-sm mt-6 space-x-6">
                    <div class="flex items-center">
                        <span class="mr-2">📅</span>
                        @if($content->published_at)
                            Published {{ $content->published_at->format('M j, Y') }}
                        @else
                            Created {{ $content->created_at->format('M j, Y') }}
                        @endif
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2">📝</span>
                        {{ ucfirst($content->type) }}
                    </div>
                    @if($content->is_featured)
                        <div class="flex items-center">
                            <span class="mr-2">⭐</span>
                            Featured
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Body -->
        <div class="p-8">
            <div class="max-w-5xl mx-auto">
                <div class="prose prose-lg max-w-none">
                    @if($content->content)
                        {!! $content->content !!}
                    @else
                        <div class="text-center py-12">
                            <span class="text-6xl">📄</span>
                            <p class="text-gray-500 mt-4 text-lg">No content yet.</p>
                            <p class="text-gray-400">Add some content in the editor to see it here.</p>
                        </div>
                    @endif
                </div>

                @if($content->mediaAssets && $content->mediaAssets->count() > 0)
                    <div class="mt-12 pt-8 border-t">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Attached Media</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($content->mediaAssets as $media)
                                <div class="bg-gray-100 rounded-lg p-4 text-center">
                                    @if($media->type === 'image')
                                        <span class="text-3xl">🖼️</span>
                                    @elseif($media->type === 'document')
                                        <span class="text-3xl">📄</span>
                                    @else
                                        <span class="text-3xl">📁</span>
                                    @endif
                                    <p class="text-sm text-gray-600 mt-2">{{ $media->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- SEO Preview (placeholder) -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h3 class="font-medium text-gray-900 mb-4">📊 SEO Preview</h3>
        <div class="border border-gray-200 rounded bg-white p-4">
            <h4 class="text-blue-600 text-lg font-medium hover:underline cursor-pointer">
                {{ $content->title }}
            </h4>
            <p class="text-green-600 text-sm">{{ url('/') }}{{ $content->slug }}</p>
            <p class="text-gray-600 text-sm mt-1">
                {{ $content->excerpt ?: Str::limit(strip_tags($content->content), 150) }}
            </p>
        </div>
        <p class="text-xs text-gray-500 mt-2">This is how your content might appear in search engine results.</p>
    </div>
</div>
@endsection