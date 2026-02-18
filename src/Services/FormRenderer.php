<?php

namespace Westlinks\Wlcms\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class FormRenderer
{
    /**
     * Form registry instance.
     *
     * @var FormRegistry
     */
    protected FormRegistry $registry;

    /**
     * Constructor.
     *
     * @param FormRegistry $registry
     */
    public function __construct(FormRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Render a form by identifier.
     *
     * @param string $identifier
     * @param array $attributes Additional HTML attributes
     * @return string
     */
    public function render(string $identifier, array $attributes = []): string
    {
        $form = $this->registry->get($identifier);

        if (!$form) {
            return $this->renderError("Form '{$identifier}' not found.");
        }

        return match ($form['type']) {
            'built-in' => $this->renderBuiltIn($form, $attributes),
            'custom' => $this->renderCustom($form, $attributes),
            'external' => $this->renderExternal($form, $attributes),
            default => $this->renderError("Unknown form type: {$form['type']}")
        };
    }

    /**
     * Render a built-in form.
     *
     * @param array $form
     * @param array $attributes
     * @return string
     */
    protected function renderBuiltIn(array $form, array $attributes): string
    {
        $viewName = $form['view'] ?? 'wlcms::forms.' . $form['identifier'];

        if (!View::exists($viewName)) {
            $viewName = 'wlcms::forms.default';
        }

        return View::make($viewName, [
            'form' => $form,
            'attributes' => $attributes,
            'action' => route('wlcms.forms.submit', ['form' => $form['identifier']]),
            'method' => $attributes['method'] ?? 'POST',
        ])->render();
    }

    /**
     * Render a custom form using a custom view.
     *
     * @param array $form
     * @param array $attributes
     * @return string
     */
    protected function renderCustom(array $form, array $attributes): string
    {
        if (empty($form['view'])) {
            return $this->renderError("Custom form '{$form['identifier']}' has no view specified.");
        }

        if (!View::exists($form['view'])) {
            return $this->renderError("View '{$form['view']}' not found.");
        }

        return View::make($form['view'], [
            'form' => $form,
            'attributes' => $attributes,
            'action' => route('wlcms.forms.submit', ['form' => $form['identifier']]),
        ])->render();
    }

    /**
     * Render an external form (iframe).
     *
     * @param array $form
     * @param array $attributes
     * @return string
     */
    protected function renderExternal(array $form, array $attributes): string
    {
        $url = $form['settings']['external_url'] ?? '';

        if (empty($url)) {
            return $this->renderError("External form '{$form['identifier']}' has no URL specified.");
        }

        $width = $attributes['width'] ?? $form['settings']['width'] ?? '100%';
        $height = $attributes['height'] ?? $form['settings']['height'] ?? '600px';
        $title = $form['name'];

        return sprintf(
            '<iframe src="%s" width="%s" height="%s" title="%s" frameborder="0" loading="lazy" class="wlcms-external-form"></iframe>',
            e($url),
            e($width),
            e($height),
            e($title)
        );
    }

    /**
     * Render an error message.
     *
     * @param string $message
     * @return string
     */
    protected function renderError(string $message): string
    {
        if (config('app.debug')) {
            return sprintf(
                '<div class="wlcms-form-error" style="padding: 1rem; background: #fee; border: 1px solid #fcc; border-radius: 4px; color: #c00;">%s</div>',
                e($message)
            );
        }

        return '<!-- Form render error: ' . e($message) . ' -->';
    }

    /**
     * Render form fields from field definition array.
     *
     * @param array $fields
     * @return string
     */
    public function renderFields(array $fields): string
    {
        $html = '';

        foreach ($fields as $field) {
            $html .= $this->renderField($field);
        }

        return $html;
    }

    /**
     * Render a single form field.
     *
     * @param array $field
     * @return string
     */
    protected function renderField(array $field): string
    {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? ucfirst($name);
        $required = $field['required'] ?? false;
        $placeholder = $field['placeholder'] ?? '';
        $value = old($name, $field['value'] ?? '');

        $requiredAttr = $required ? 'required' : '';
        $requiredMark = $required ? '<span class="text-red-600">*</span>' : '';

        return match ($type) {
            'textarea' => sprintf(
                '<div class="mb-4"><label for="%s" class="block text-sm font-medium text-gray-700 mb-1">%s %s</label><textarea name="%s" id="%s" rows="%d" placeholder="%s" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" %s>%s</textarea></div>',
                e($name),
                e($label),
                $requiredMark,
                e($name),
                e($name),
                $field['rows'] ?? 4,
                e($placeholder),
                $requiredAttr,
                e($value)
            ),
            'select' => $this->renderSelectField($field),
            'checkbox' => $this->renderCheckboxField($field),
            'radio' => $this->renderRadioField($field),
            default => sprintf(
                '<div class="mb-4"><label for="%s" class="block text-sm font-medium text-gray-700 mb-1">%s %s</label><input type="%s" name="%s" id="%s" value="%s" placeholder="%s" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" %s></div>',
                e($name),
                e($label),
                $requiredMark,
                e($type),
                e($name),
                e($name),
                e($value),
                e($placeholder),
                $requiredAttr
            ),
        };
    }

    /**
     * Render a select field.
     *
     * @param array $field
     * @return string
     */
    protected function renderSelectField(array $field): string
    {
        $name = $field['name'];
        $label = $field['label'] ?? ucfirst($name);
        $required = $field['required'] ?? false;
        $options = $field['options'] ?? [];
        $value = old($name, $field['value'] ?? '');

        $requiredAttr = $required ? 'required' : '';
        $requiredMark = $required ? '<span class="text-red-600">*</span>' : '';

        $optionsHtml = '';
        foreach ($options as $optValue => $optLabel) {
            $selected = $value == $optValue ? 'selected' : '';
            $optionsHtml .= sprintf('<option value="%s" %s>%s</option>', e($optValue), $selected, e($optLabel));
        }

        return sprintf(
            '<div class="mb-4"><label for="%s" class="block text-sm font-medium text-gray-700 mb-1">%s %s</label><select name="%s" id="%s" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" %s>%s</select></div>',
            e($name),
            e($label),
            $requiredMark,
            e($name),
            e($name),
            $requiredAttr,
            $optionsHtml
        );
    }

    /**
     * Render a checkbox field.
     *
     * @param array $field
     * @return string
     */
    protected function renderCheckboxField(array $field): string
    {
        $name = $field['name'];
        $label = $field['label'] ?? ucfirst($name);
        $required = $field['required'] ?? false;
        $checked = old($name, $field['checked'] ?? false) ? 'checked' : '';

        $requiredAttr = $required ? 'required' : '';

        return sprintf(
            '<div class="mb-4"><label class="flex items-center"><input type="checkbox" name="%s" id="%s" value="1" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500" %s %s><span class="text-sm text-gray-700">%s</span></label></div>',
            e($name),
            e($name),
            $checked,
            $requiredAttr,
            e($label)
        );
    }

    /**
     * Render a radio field.
     *
     * @param array $field
     * @return string
     */
    protected function renderRadioField(array $field): string
    {
        $name = $field['name'];
        $label = $field['label'] ?? ucfirst($name);
        $required = $field['required'] ?? false;
        $options = $field['options'] ?? [];
        $value = old($name, $field['value'] ?? '');

        $requiredAttr = $required ? 'required' : '';
        $requiredMark = $required ? '<span class="text-red-600">*</span>' : '';

        $optionsHtml = '';
        foreach ($options as $optValue => $optLabel) {
            $checked = $value == $optValue ? 'checked' : '';
            $optionsHtml .= sprintf(
                '<label class="flex items-center mb-2"><input type="radio" name="%s" value="%s" class="mr-2 border-gray-300 text-blue-600 focus:ring-blue-500" %s %s><span class="text-sm text-gray-700">%s</span></label>',
                e($name),
                e($optValue),
                $checked,
                $requiredAttr,
                e($optLabel)
            );
        }

        return sprintf(
            '<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">%s %s</label><div>%s</div></div>',
            e($label),
            $requiredMark,
            $optionsHtml
        );
    }
}
