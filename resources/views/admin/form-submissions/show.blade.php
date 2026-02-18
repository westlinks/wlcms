<x-admin-layout title="View Form Submission">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Form Submission') }} #{{ $submission->id }}
            </h2>
            <a href="{{ route('wlcms.admin.form-submissions.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $submission->form_name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Submitted {{ $submission->submitted_at->diffForHumans() }} 
                                ({{ $submission->submitted_at->format('M d, Y H:i:s') }})
                            </p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            {{ $submission->status === 'unread' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $submission->status === 'read' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $submission->status === 'archived' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ ucfirst($submission->status) }}
                        </span>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Submission ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $submission->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $submission->ip_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Form Identifier</dt>
                            <dd class="mt-1 text-sm text-gray-900"><code class="bg-gray-100 px-2 py-1 rounded">{{ $submission->form_identifier }}</code></dd>
                        </div>
                    </dl>
                    @if($submission->user_agent)
                        <div class="mt-4">
                            <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">{{ $submission->user_agent }}</dd>
                        </div>
                    @endif
                </div>

                <!-- Form Data -->
                <div class="px-6 py-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Submitted Data</h4>
                    <dl class="space-y-4">
                        @foreach($submission->data as $key => $value)
                            <div class="border-b border-gray-200 pb-4 last:border-0">
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                </dt>
                                <dd class="text-sm text-gray-900">
                                    @if(is_array($value))
                                        <ul class="list-disc list-inside">
                                            @foreach($value as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        @if(filter_var($value, FILTER_VALIDATE_EMAIL))
                                            <a href="mailto:{{ $value }}" class="text-blue-600 hover:text-blue-800">{{ $value }}</a>
                                        @elseif(filter_var($value, FILTER_VALIDATE_URL))
                                            <a href="{{ $value }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $value }}</a>
                                        @else
                                            <div class="whitespace-pre-wrap">{{ $value }}</div>
                                        @endif
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <div class="flex space-x-2">
                        <form action="{{ route('wlcms.admin.form-submissions.status', $submission) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="unread">
                            <button type="submit" class="px-3 py-2 bg-blue-100 text-blue-700 text-sm rounded hover:bg-blue-200">
                                Mark as Unread
                            </button>
                        </form>
                        <form action="{{ route('wlcms.admin.form-submissions.status', $submission) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="read">
                            <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                                Mark as Read
                            </button>
                        </form>
                        <form action="{{ route('wlcms.admin.form-submissions.status', $submission) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="archived">
                            <button type="submit" class="px-3 py-2 bg-yellow-100 text-yellow-700 text-sm rounded hover:bg-yellow-200">
                                Archive
                            </button>
                        </form>
                    </div>
                    
                    <form action="{{ route('wlcms.admin.form-submissions.destroy', $submission) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this submission? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-100 text-red-700 text-sm rounded hover:bg-red-200">
                            Delete Submission
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
