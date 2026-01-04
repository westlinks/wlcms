<x-wlcms::admin-layout :title="$content->title . ' - WLCMS Admin'" :page-title="$content->title">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Content Details</h3>
            <p class="text-gray-600">{{ ucfirst($content->type) }} • {{ ucfirst($content->status) }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('wlcms.admin.content.edit', $content) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                ✏️ Edit
            </a>
            <a href="{{ route('wlcms.admin.content.index') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                ← Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $content->title }}</h2>
                
                @if($content->excerpt)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="font-medium text-gray-900 mb-2">Excerpt</h3>
                        <p class="text-gray-700">{{ $content->excerpt }}</p>
                    </div>
                @endif

                <div class="prose max-w-none">
                    {!! $content->content ?? '<p class="text-gray-500 italic">No content yet.</p>' !!}
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-medium text-gray-900 mb-4">Status</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Current Status:</span>
                        <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($content->status === 'published') bg-green-100 text-green-800
                            @elseif($content->status === 'draft') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($content->status) }}
                        </span>
                    </div>
                    
                    @if($content->published_at)
                        <div>
                            <span class="text-sm text-gray-600">Published:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $content->published_at->format('M j, Y g:i A') }}</span>
                        </div>
                    @endif

                    <div>
                        <span class="text-sm text-gray-600">Created:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ $content->created_at->format('M j, Y g:i A') }}</span>
                    </div>

                    <div>
                        <span class="text-sm text-gray-600">Updated:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ $content->updated_at->format('M j, Y g:i A') }}</span>
                    </div>
                </div>

                @if($content->status !== 'published')
                    <form method="POST" action="{{ route('wlcms.admin.content.publish', $content) }}" class="mt-4">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            📢 Publish Now
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('wlcms.admin.content.unpublish', $content) }}" class="mt-4">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            📝 Move to Draft
                        </button>
                    </form>
                @endif
            </div>

            <!-- Meta Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-medium text-gray-900 mb-4">Content Info</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Type:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ ucfirst($content->type) }}</span>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Slug:</span>
                        <span class="ml-2 text-sm text-gray-900 font-mono">{{ $content->slug }}</span>
                    </div>

                    @if($content->sort_order)
                        <div>
                            <span class="text-sm text-gray-600">Sort Order:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $content->sort_order }}</span>
                        </div>
                    @endif

                    @if($content->is_featured)
                        <div>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                ⭐ Featured Content
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-medium text-gray-900 mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('wlcms.admin.content.revisions', $content) }}" 
                       class="block w-full px-4 py-2 text-center bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        📋 View Revisions
                    </a>
                    <a href="{{ route('wlcms.admin.content.preview', $content) }}" 
                       class="block w-full px-4 py-2 text-center bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                        👁️ Preview
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-wlcms::admin-layout>