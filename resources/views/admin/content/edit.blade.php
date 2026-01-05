<x-admin-layout title="Edit Content">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Content') }}
        </h2>
    </x-slot>
    <form method="POST" action="{{ route('wlcms.admin.content.update', $content) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3 space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" required
                               value="{{ old('title', $content->title) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div>
                        @include('wlcms::admin.components.editor', [
                            'name' => 'content',
                            'value' => old('content', $content->content),
                            'label' => 'Content',
                            'required' => false
                        ])
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Brief description or excerpt...">{{ old('excerpt', $content->excerpt) }}</textarea>
                        @error('excerpt')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Publish Settings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Publish Settings</h3>
                        
                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="draft" {{ old('status', $content->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $content->status) === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="scheduled" {{ old('status', $content->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="archived" {{ old('status', $content->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Content Type</label>
                                <select name="type" id="type" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="page" {{ old('type', $content->type) === 'page' ? 'selected' : '' }}>Page</option>
                                    <option value="post" {{ old('type', $content->type) === 'post' ? 'selected' : '' }}>Post</option>
                                    <option value="article" {{ old('type', $content->type) === 'article' ? 'selected' : '' }}>Article</option>
                                    <option value="news" {{ old('type', $content->type) === 'news' ? 'selected' : '' }}>News</option>
                                    <option value="event" {{ old('type', $content->type) === 'event' ? 'selected' : '' }}>Event</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t space-y-3">
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                💾 Update Content
                            </button>
                            <a href="{{ route('wlcms.admin.content.show', $content) }}" 
                               class="block w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium text-center">
                                Cancel
                            </a>
                        </div>
                    </div>

                    <!-- Content Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Content Info</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="text-gray-600">Slug:</span>
                                <span class="ml-2 text-gray-900 font-mono">{{ $content->slug }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Created:</span>
                                <span class="ml-2 text-gray-900">{{ $content->created_at->format('M j, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="ml-2 text-gray-900">{{ $content->updated_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-2">
                            <a href="{{ route('wlcms.admin.content.preview', $content) }}" 
                               class="block w-full px-3 py-2 text-center text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                👁️ Preview Changes
                            </a>
                            <a href="{{ route('wlcms.admin.content.revisions', $content) }}" 
                               class="block w-full px-3 py-2 text-center text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                📋 View Revisions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>