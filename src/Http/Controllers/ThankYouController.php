<?php

namespace Westlinks\Wlcms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Services\FormRegistry;

class ThankYouController extends Controller
{
    /**
     * Display the thank you page for a form submission.
     *
     * @param string $form Form identifier
     * @return \Illuminate\View\View
     */
    public function show(string $form)
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
