<div class="wlcms-form-container max-w-2xl mx-auto">
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form 
        action="{{ $action }}" 
        method="{{ $method }}" 
        class="wlcms-form space-y-4"
        {!! isset($attributes['class']) ? 'class="' . e($attributes['class']) . '"' : '' !!}
    >
        @csrf

        @foreach ($form['fields'] as $field)
            @php
                $fieldName = $field['name'];
                $fieldType = $field['type'];
                $fieldLabel = $field['label'] ?? ucfirst($fieldName);
                $fieldRequired = $field['required'] ?? false;
                $fieldPlaceholder = $field['placeholder'] ?? '';
                $fieldValue = old($fieldName, $field['value'] ?? '');
            @endphp

            @if ($fieldType === 'textarea')
                <div class="form-group">
                    <label for="{{ $fieldName }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $fieldLabel }}
                        @if ($fieldRequired)
                            <span class="text-red-600">*</span>
                        @endif
                    </label>
                    <textarea
                        name="{{ $fieldName }}"
                        id="{{ $fieldName }}"
                        rows="{{ $field['rows'] ?? 4 }}"
                        placeholder="{{ $fieldPlaceholder }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror"
                        {{ $fieldRequired ? 'required' : '' }}
                    >{{ $fieldValue }}</textarea>
                    @error($fieldName)
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            @elseif ($fieldType === 'select')
                <div class="form-group">
                    <label for="{{ $fieldName }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $fieldLabel }}
                        @if ($fieldRequired)
                            <span class="text-red-600">*</span>
                        @endif
                    </label>
                    <select
                        name="{{ $fieldName }}"
                        id="{{ $fieldName }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror"
                        {{ $fieldRequired ? 'required' : '' }}
                    >
                        <option value="">Select an option...</option>
                        @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" {{ $fieldValue == $optValue ? 'selected' : '' }}>
                                {{ $optLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error($fieldName)
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            @elseif ($fieldType === 'checkbox')
                <div class="form-group">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="{{ $fieldName }}"
                            id="{{ $fieldName }}"
                            value="1"
                            class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            {{ old($fieldName, $field['checked'] ?? false) ? 'checked' : '' }}
                            {{ $fieldRequired ? 'required' : '' }}
                        >
                        <span class="text-sm text-gray-700">{{ $fieldLabel }}</span>
                        @if ($fieldRequired)
                            <span class="text-red-600 ml-1">*</span>
                        @endif
                    </label>
                    @error($fieldName)
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            @else
                <div class="form-group">
                    <label for="{{ $fieldName }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $fieldLabel }}
                        @if ($fieldRequired)
                            <span class="text-red-600">*</span>
                        @endif
                    </label>
                    <input
                        type="{{ $fieldType }}"
                        name="{{ $fieldName }}"
                        id="{{ $fieldName }}"
                        value="{{ $fieldValue }}"
                        placeholder="{{ $fieldPlaceholder }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror"
                        {{ $fieldRequired ? 'required' : '' }}
                    >
                    @error($fieldName)
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        @endforeach

        <div class="form-actions pt-4">
            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200"
            >
                Submit
            </button>
        </div>
    </form>
</div>
