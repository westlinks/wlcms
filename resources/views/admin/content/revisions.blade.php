@extends('wlcms::admin.layout')

@section('title', 'Revisions: ' . $content->title . ' - WLCMS Admin')
@section('page-title', 'Content Revisions')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Content Revisions</h3>
            <p class="text-gray-600">{{ $content->title }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('wlcms.admin.content.edit', $content) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                ✏️ Edit Content
            </a>
            <a href="{{ route('wlcms.admin.content.show', $content) }}" 
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                ← Back to Content
            </a>
        </div>
    </div>

    @if($revisions->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h4 class="font-medium text-gray-900">Revision History</h4>
                <p class="text-sm text-gray-600">Track all changes made to this content over time.</p>
            </div>
            
            <div class="divide-y divide-gray-200">
                @foreach($revisions as $revision)
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Revision #{{ $revision->revision_number }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ $revision->created_at->format('M j, Y g:i A') }}
                                    </span>
                                    @if($revision->created_by)
                                        <span class="text-sm text-gray-500">
                                            by {{ $revision->created_by }}
                                        </span>
                                    @endif
                                </div>
                                
                                @if($revision->change_summary)
                                    <p class="text-sm text-gray-700 mb-3">{{ $revision->change_summary }}</p>
                                @endif
                                
                                <div class="text-sm">
                                    <h5 class="font-medium text-gray-900 mb-1">{{ $revision->title }}</h5>
                                    @if($revision->excerpt)
                                        <p class="text-gray-600 mb-2">{{ Str::limit($revision->excerpt, 100) }}</p>
                                    @endif
                                    <p class="text-gray-500">{{ Str::limit(strip_tags($revision->content), 150) }}</p>
                                </div>
                            </div>
                            
                            <div class="ml-4 flex space-x-2">
                                <button type="button" 
                                        onclick="previewRevision({{ $revision->id }})"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    👁️ Preview
                                </button>
                                <button type="button" 
                                        onclick="restoreRevision({{ $revision->id }})"
                                        class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    ↩️ Restore
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Revision Actions Modal (placeholder) -->
        <div id="revision-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg max-w-2xl w-full mx-4 p-6">
                <h3 class="text-lg font-semibold mb-4">Revision Action</h3>
                <div id="revision-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeRevisionModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="button" 
                            onclick="confirmRevisionAction()"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- No Revisions State -->
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <span class="text-6xl">📋</span>
            <h3 class="text-lg font-semibold text-gray-900 mt-4">No revisions yet</h3>
            <p class="text-gray-600 mt-2">Revisions will be created automatically when you make changes to this content.</p>
            <div class="mt-6">
                <a href="{{ route('wlcms.admin.content.edit', $content) }}" 
                   class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Make Your First Edit
                </a>
            </div>
        </div>
    @endif
</div>

<script>
function previewRevision(revisionId) {
    document.getElementById('revision-content').innerHTML = '<p>Revision preview functionality coming soon!</p>';
    document.getElementById('revision-modal').classList.remove('hidden');
}

function restoreRevision(revisionId) {
    document.getElementById('revision-content').innerHTML = 
        '<p><strong>Are you sure?</strong></p><p>This will restore the content to revision #' + revisionId + ' and create a new revision with the current content.</p>';
    document.getElementById('revision-modal').classList.remove('hidden');
}

function closeRevisionModal() {
    document.getElementById('revision-modal').classList.add('hidden');
}

function confirmRevisionAction() {
    // Placeholder for actual restoration logic
    alert('Revision functionality will be implemented in Phase 2!');
    closeRevisionModal();
}
</script>
@endsection