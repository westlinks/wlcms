{{-- TEMPORARY: Force simple layout without navigation --}}
<!DOCTYPE html>
<html>
<head>
    <title>WLCMS Content</title>
    @vite(['resources/vendor/wlcms/css/wlcms.css'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm border-b p-4">
            <h1 class="text-2xl font-semibold text-gray-800">Content Management</h1>
        </header>
        <main class="p-6">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div class="flex space-x-4">
                {{-- Filter Buttons --}}
                <div class="flex rounded-lg border">
                    <a href="{{ route('wlcms.admin.content.index') }}"
                       class="px-4 py-2 {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} rounded-l-lg border-r">
                        All ({{ $stats['total'] }})
                    </a>
                    <a href="{{ route('wlcms.admin.content.index', ['status' => 'published']) }}"
                       class="px-4 py-2 {{ request('status') === 'published' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border-r">
                        Published ({{ $stats['published'] }})
                    </a>
                    <a href="{{ route('wlcms.admin.content.index', ['status' => 'draft']) }}"
                       class="px-4 py-2 {{ request('status') === 'draft' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border-r">
                        Drafts ({{ $stats['draft'] }})
                    </a>
                    <a href="{{ route('wlcms.admin.content.index', ['status' => 'archived']) }}"
                       class="px-4 py-2 {{ request('status') === 'archived' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} rounded-r-lg">
                        Archived ({{ $stats['archived'] }})
                    </a>
                </div>

                {{-- Type Filter --}}
                <select class="border rounded-lg px-3 py-2 bg-white" onchange="window.location.href = this.value">
                    <option value="{{ route('wlcms.admin.content.index', array_merge(request()->all(), ['type' => null])) }}"
                            {{ !request('type') ? 'selected' : '' }}>
                        All Types
                    </option>
                    <option value="{{ route('wlcms.admin.content.index', array_merge(request()->all(), ['type' => 'page'])) }}"
                            {{ request('type') === 'page' ? 'selected' : '' }}>
                        Pages
                    </option>
                    <option value="{{ route('wlcms.admin.content.index', array_merge(request()->all(), ['type' => 'post'])) }}"
                            {{ request('type') === 'post' ? 'selected' : '' }}>
                        Posts
                    </option>
                    <option value="{{ route('wlcms.admin.content.index', array_merge(request()->all(), ['type' => 'article'])) }}"
                            {{ request('type') === 'article' ? 'selected' : '' }}>
                        Articles
                    </option>
                </select>
            </div>

            <a href="{{ route('wlcms.admin.content.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center">
                <span class="mr-2">+</span>
                Create Content
            </a>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="mb-6">
        <form method="GET" class="flex gap-4">
            @foreach(request()->except(['search', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search content..."
                       class="w-full border rounded-lg px-4 py-2">
            </div>
            <button type="submit" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('wlcms.admin.content.index', request()->except(['search', 'page'])) }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Content Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($content->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($content as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $item->title }}</div>
                                    @if($item->slug)
                                        <div class="text-sm text-gray-500">{{ $item->slug }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $item->type === 'page' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $item->type === 'post' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $item->type === 'article' ? 'bg-purple-100 text-purple-800' : '' }}">
                                    {{ ucfirst($item->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $item->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $item->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $item->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($item->creator ?? null)
                                    {{ $item->creator_name }}
                                @else
                                    System
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div>{{ $item->updated_at->format('M j, Y') }}</div>
                                <div class="text-xs">{{ $item->updated_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium space-x-2">
                                <a href="{{ route('wlcms.admin.content.show', $item) }}"
                                   class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="{{ route('wlcms.admin.content.edit', $item) }}"
                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                @if($item->hasRevisions())
                                    <a href="{{ route('wlcms.admin.content.revisions', $item) }}"
                                       class="text-purple-600 hover:text-purple-900">History</a>
                                @endif
                                <form method="POST" action="{{ route('wlcms.admin.content.destroy', $item) }}"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this content?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t">
                {{ $content->appends(request()->all())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <span class="text-6xl">📝</span>
                <h3 class="text-lg font-medium text-gray-900 mt-4">No content found</h3>
                <p class="text-gray-600 mt-2">
                    @if(request('search') || request('status') || request('type'))
                        Try adjusting your filters or search terms.
                    @else
                        Get started by creating your first piece of content.
                    @endif
                </p>
                @if(!request('search') && !request('status') && !request('type'))
                    <a href="{{ route('wlcms.admin.content.create') }}"
                       class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Create Content
                    </a>
                @endif
            </div>
        @endif
    </div>
        </main>
    </div>
</body>
</html>