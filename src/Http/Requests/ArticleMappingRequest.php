<?php

namespace Westlinks\Wlcms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleMappingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'legacy_article_id' => 'required|integer|min:1',
            'sync_direction' => 'required|in:legacy_to_cms,cms_to_legacy,bidirectional',
            'auto_sync' => 'boolean',
            'preserve_legacy_urls' => 'boolean',
            'field_mappings' => 'nullable|array',
            'field_mappings.*' => 'nullable|string|max:500',
        ];

        // Add CMS content validation for create requests
        if ($this->isMethod('POST')) {
            $rules['cms_content_id'] = 'required|exists:cms_content_items,id';
            
            // Ensure the legacy article isn't already mapped
            $rules['legacy_article_id'] .= '|unique:cms_legacy_article_mappings,legacy_article_id';
        }

        // Add status validation for update requests
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = 'required|in:active,inactive,error';
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'legacy_article_id.required' => 'Please select a legacy article to map.',
            'legacy_article_id.unique' => 'This legacy article is already mapped to CMS content.',
            'cms_content_id.required' => 'Please select CMS content to map to.',
            'cms_content_id.exists' => 'The selected CMS content does not exist.',
            'sync_direction.required' => 'Please specify the synchronization direction.',
            'sync_direction.in' => 'Invalid synchronization direction selected.',
            'field_mappings.array' => 'Field mappings must be provided as an array.',
            'field_mappings.*.max' => 'Field mapping values cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'legacy_article_id' => 'legacy article',
            'cms_content_id' => 'CMS content',
            'sync_direction' => 'synchronization direction',
            'auto_sync' => 'automatic synchronization',
            'preserve_legacy_urls' => 'preserve legacy URLs',
            'field_mappings' => 'field mappings',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string booleans to actual booleans
        if ($this->has('auto_sync')) {
            $this->merge([
                'auto_sync' => filter_var($this->auto_sync, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('preserve_legacy_urls')) {
            $this->merge([
                'preserve_legacy_urls' => filter_var($this->preserve_legacy_urls, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}