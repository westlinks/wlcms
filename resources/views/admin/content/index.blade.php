@extends('wlcms::admin.layout')

@section('title', 'Content - WLCMS Admin')
@section('page-title', 'Content Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">All Content</h3>
    <a href="{{ route('wlcms.admin.content.create') }}" 
       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        ➕ Create New
    </a>
</div>

@if($content->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($content as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ $item->title }}</div>
                                @if($item->excerpt)
                                    <div class="text-sm text-gray-600">{{ Str::limit($item->excerpt, 60) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ ucfirst($item->type) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                @if($item->status === 'published') bg-green-100 text-green-800
                                @elseif($item->status === 'draft') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $item->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 space-x-2">
                            <a href="{{ route('wlcms.admin.content.show', $item) }}" 
                               class="text-blue-600 hover:text-blue-800">View</a>
                            <a href="{{ route('wlcms.admin.content.edit', $item) }}" 
                               class="text-green-600 hover:text-green-800">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $content->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <span class="text-6xl">📝</span>
        <h3 class="text-lg font-semibold text-gray-900 mt-4">No content yet</h3>
        <p class="text-gray-600 mt-2">Get started by creating your first piece of content.</p>
        <a href="{{ route('wlcms.admin.content.create') }}" 
           class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            Create Content
        </a>
    </div>
@endif
@endsection