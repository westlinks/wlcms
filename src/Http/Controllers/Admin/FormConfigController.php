<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Services\FormRegistry;

class FormConfigController extends Controller
{
    /**
     * Display a listing of all forms.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $formRegistry = app(FormRegistry::class);
        $forms = $formRegistry->all();

        return view('wlcms::admin.forms.index', compact('forms'));
    }

    /**
     * Show the form for editing a form's thank you page.
     *
     * @param string $form
     * @return \Illuminate\View\View
     */
    public function edit(string $form)
    {
        $formRegistry = app(FormRegistry::class);
        $formConfig = $formRegistry->get($form);

        if (!$formConfig) {
            abort(404, 'Form not found');
        }

        return view('wlcms::admin.forms.edit', [
            'form' => $formConfig,
        ]);
    }

    /**
     * Update the thank you page configuration for a form.
     *
     * Note: This updates the runtime configuration only.
     * For persistent changes, edit WlcmsServiceProvider.php
     *
     * @param Request $request
     * @param string $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $form)
    {
        $formRegistry = app(FormRegistry::class);
        $formConfig = $formRegistry->get($form);

        if (!$formConfig) {
            abort(404, 'Form not found');
        }

        $validated = $request->validate([
            'thank_you_title' => 'required|string|max:255',
            'thank_you_content' => 'required|string',
            'success_message' => 'required|string|max:500',
        ]);

        // Update the form configuration in the registry
        $formRegistry->register($form, array_merge($formConfig, $validated));

        // Store in cache for persistence across requests
        cache()->put("wlcms.form.{$form}.config", $validated, now()->addYear());

        return redirect()
            ->route('wlcms.admin.forms.edit', $form)
            ->with('success', 'Form thank you page updated successfully!');
    }

    /**
     * Preview the thank you page for a form.
     *
     * @param string $form
     * @return \Illuminate\View\View
     */
    public function preview(string $form)
    {
        $formRegistry = app(FormRegistry::class);
        $formConfig = $formRegistry->get($form);

        if (!$formConfig) {
            abort(404, 'Form not found');
        }

        return view('wlcms::forms.thank-you', [
            'form' => $formConfig,
            'title' => $formConfig['thank_you_title'] ?? 'Thank You!',
            'content' => $formConfig['thank_you_content'] ?? '<p>Your submission has been received.</p>',
        ]);
    }
}
