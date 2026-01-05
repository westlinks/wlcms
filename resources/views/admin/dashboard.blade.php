@if(config('wlcms.layout.mode') === 'embedded')
    <x-admin-layout title="CMS Dashboard">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>
@else
    <x-wlcms-admin-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>
@endif

    {{-- WLCMS Styles --}}
    @push('styles')
        @vite(['resources/vendor/wlcms/css/wlcms.css'])
    @endpush
    <div class="space-y-6">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <span class="text-2xl">📝</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Total Content</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_content'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <span class="text-2xl">✅</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Published</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['published_content'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <span class="text-2xl">✏️</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Drafts</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['draft_content'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <span class="text-2xl">📁</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Media Files</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_media'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Content and Media Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Content -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Content</h3>
                </div>
                <div class="p-6">
                    @if($stats['recent_content']->count() > 0)
                        <div class="space-y-4">
                            @foreach($stats['recent_content'] as $content)
                                <div class="flex items-center justify-between py-2 border-b last:border-0">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $content->title }}</h4>
                                        <p class="text-sm text-gray-600">{{ ucfirst($content->type) }} • {{ ucfirst($content->status) }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $content->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('wlcms.admin.content.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                View all content →
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="text-4xl">📝</span>
                            <p class="text-gray-600 mt-2">No content yet</p>
                            <a href="{{ route('wlcms.admin.content.create') }}" 
                               class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Create your first content
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Media -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Media</h3>
                </div>
                <div class="p-6">
                    @if($stats['recent_media']->count() > 0)
                        <div class="space-y-4">
                            @foreach($stats['recent_media'] as $media)
                                <div class="flex items-center justify-between py-2 border-b last:border-0">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center overflow-hidden">
                                            @if($media->type === 'image')
                                                @if($media->getThumbnailUrl('thumb'))
                                                    <img src="{{ $media->getThumbnailUrl('thumb') }}" 
                                                         alt="{{ $media->name }}" 
                                                         class="w-full h-full object-cover rounded">
                                                @else
                                                    <img src="{{ $media->url }}" 
                                                         alt="{{ $media->name }}" 
                                                         class="w-full h-full object-cover rounded">
                                                @endif
                                            @elseif($media->type === 'document')
                                                <span class="text-lg">📄</span>
                                            @else
                                                <span class="text-lg">📁</span>
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="font-medium text-gray-900">{{ $media->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ ucfirst($media->type) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $media->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('wlcms.admin.media.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                View all media →
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="text-4xl">📁</span>
                            <p class="text-gray-600 mt-2">No media files yet</p>
                            <a href="{{ route('wlcms.admin.media.index') }}" 
                               class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Upload media
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@if(config('wlcms.layout.mode') === 'embedded')
    </x-admin-layout>
@else
    </x-wlcms-admin-layout>
@endif