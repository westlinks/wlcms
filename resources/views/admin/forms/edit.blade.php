@extends('wlcms::layouts.admin')

@section('title', 'Edit Form Thank You Page')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Thank You Page</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $form['name'] }} ({{ $form['identifier'] }})
                    </p>
                </div>
                <a href="{{ route('wlcms.admin.forms.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Forms
                </a>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Edit Form --}}
        <form action="{{ route('wlcms.admin.forms.update', $form['identifier']) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6 space-y-6">
                    {{-- Thank You Title --}}
                    <div>
                        <label for="thank_you_title" class="block text-sm font-medium text-gray-700 mb-2">
                            Thank You Page Title *
                        </label>
                        <input type="text" 
                               name="thank_you_title" 
                               id="thank_you_title" 
                               value="{{ old('thank_you_title', $form['thank_you_title'] ?? 'Thank You!') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               required>
                        @error('thank_you_title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Thank You Content --}}
                    <div>
                        <label for="thank_you_content" class="block text-sm font-medium text-gray-700 mb-2">
                            Thank You Page Content *
                        </label>
                        <div class="border border-gray-300 rounded-md" id="thank_you_content_editor"></div>
                        <textarea name="thank_you_content" 
                                  id="thank_you_content" 
                                  class="hidden"
                                  required>{{ old('thank_you_content', $form['thank_you_content'] ?? '') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            This content will be displayed on the thank you page after form submission.
                        </p>
                        @error('thank_you_content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Success Message (Flash) --}}
                    <div>
                        <label for="success_message" class="block text-sm font-medium text-gray-700 mb-2">
                            Success Flash Message *
                        </label>
                        <textarea name="success_message" 
                                  id="success_message" 
                                  rows="2"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  required>{{ old('success_message', $form['success_message'] ?? '') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            This message appears briefly after form submission (for AJAX forms).
                        </p>
                        @error('success_message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <a href="{{ route('wlcms.admin.forms.preview', $form['identifier']) }}" 
                       target="_blank"
                       class="text-sm text-gray-600 hover:text-gray-900">
                        Preview Thank You Page →
                    </a>
                    <div class="flex gap-3">
                        <a href="{{ route('wlcms.admin.forms.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Info Box --}}
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Note about persistence</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Changes are stored in cache and will persist until the cache is cleared. For permanent changes, edit the form registration in <code>WlcmsServiceProvider.php</code>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="module">
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('thank_you_content');
    const editorElement = document.getElementById('thank_you_content_editor');
    
    if (!editorElement || !textarea) return;

    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit,
            Link.configure({
                openOnClick: false,
            }),
        ],
        content: textarea.value,
        editorProps: {
            attributes: {
                class: 'prose prose-sm max-w-none focus:outline-none min-h-[200px] p-4',
            },
        },
        onUpdate: ({ editor }) => {
            textarea.value = editor.getHTML();
        },
    });

    // Update editor when textarea changes
    textarea.addEventListener('input', () => {
        if (editor.getHTML() !== textarea.value) {
            editor.commands.setContent(textarea.value);
        }
    });
});
</script>
@endpush
@endsection
