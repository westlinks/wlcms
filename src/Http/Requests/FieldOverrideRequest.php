<?php

namespace Westlinks\Wlcms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FieldOverrideRequest extends FormRequest
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
        return [
            'legacy_mapping_id' => 'required|exists:cms_legacy_article_mappings,id',
            'field_name' => 'required|string|max:100',
            'override_value' => 'nullable|string|max:1000',
            'data_type' => 'required|in:string,integer,boolean,json,date,datetime',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'legacy_mapping_id.required' => 'Legacy mapping is required.',
            'legacy_mapping_id.exists' => 'The selected legacy mapping does not exist.',
            'field_name.required' => 'Field name is required.',
            'field_name.max' => 'Field name cannot exceed 100 characters.',
            'override_value.max' => 'Override value cannot exceed 1000 characters.',
            'data_type.required' => 'Data type is required.',
            'data_type.in' => 'Invalid data type selected.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'legacy_mapping_id' => 'legacy mapping',
            'field_name' => 'field name',
            'override_value' => 'override value',
            'data_type' => 'data type',
            'is_active' => 'active status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string boolean to actual boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Validate override value based on data type
        if ($this->has('data_type') && $this->has('override_value')) {
            $this->validateOverrideValueByType();
        }
    }

    /**
     * Additional validation after the main validation rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->override_value !== null && $this->data_type) {
                $this->validateValueType($validator);
            }
        });
    }

    /**
     * Validate override value matches the specified data type.
     */
    protected function validateValueType($validator): void
    {
        $value = $this->override_value;
        $type = $this->data_type;

        switch ($type) {
            case 'integer':
                if (!filter_var($value, FILTER_VALIDATE_INT)) {
                    $validator->errors()->add('override_value', 'Override value must be a valid integer.');
                }
                break;

            case 'boolean':
                if (!in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no'])) {
                    $validator->errors()->add('override_value', 'Override value must be a valid boolean (true/false, 1/0, yes/no).');
                }
                break;

            case 'json':
                if (json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) {
                    $validator->errors()->add('override_value', 'Override value must be valid JSON.');
                }
                break;

            case 'date':
                if (!strtotime($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $validator->errors()->add('override_value', 'Override value must be a valid date (YYYY-MM-DD).');
                }
                break;

            case 'datetime':
                if (!strtotime($value)) {
                    $validator->errors()->add('override_value', 'Override value must be a valid datetime.');
                }
                break;
        }
    }

    /**
     * Validate override value type during preparation.
     */
    protected function validateOverrideValueByType(): void
    {
        // This method can be used for early validation if needed
        // Currently handled in withValidator method
    }
}