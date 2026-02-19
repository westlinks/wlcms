<?php

namespace Westlinks\Wlcms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Westlinks\Wlcms\Models\FormSubmission;
use Westlinks\Wlcms\Services\FormRegistry;

class FormController extends Controller
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
     * Handle form submission.
     *
     * @param Request $request
     * @param string $form
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function submit(Request $request, string $form)
    {
        $formConfig = $this->registry->get($form);

        if (!$formConfig) {
            return $this->errorResponse('Form not found.', 404);
        }

        // Validate the form data
        $validator = Validator::make(
            $request->all(),
            $formConfig['validation'] ?? []
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Call custom handler if specified
        if (!empty($formConfig['handler']) && is_callable($formConfig['handler'])) {
            $result = call_user_func($formConfig['handler'], $validated, $request);
            
            if ($result !== null) {
                return $this->handleHandlerResult($result, $formConfig, $request);
            }
        }

        // Default handling - log and optionally email
        $this->handleDefault($validated, $formConfig, $request);

        // Return response
        return $this->successResponse($formConfig, $request);
    }

    /**
     * Handle custom handler result.
     *
     * @param mixed $result
     * @param array $formConfig
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function handleHandlerResult($result, array $formConfig, Request $request)
    {
        if (is_array($result) && isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['code'] ?? 400);
        }

        if (is_array($result) && isset($result['redirect'])) {
            return redirect($result['redirect'])
                ->with('success', $result['message'] ?? $formConfig['success_message']);
        }

        return $this->successResponse($formConfig, $request);
    }

    /**
     * Default form handling - save to database, log submission data.
     *
     * @param array $data
     * @param array $formConfig
     * @param Request $request
     * @return void
     */
    protected function handleDefault(array $data, array $formConfig, Request $request): void
    {
        // Save submission to database
        $submission = FormSubmission::create([
            'form_identifier' => $formConfig['identifier'],
            'form_name' => $formConfig['name'],
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'unread',
            'submitted_at' => now(),
        ]);

        // Log the submission
        Log::info('WLCMS Form Submission', [
            'submission_id' => $submission->id,
            'form' => $formConfig['identifier'],
            'ip' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Send email notification if configured
        if (!empty($formConfig['settings']['notify_email'])) {
            $this->sendEmailNotification($data, $formConfig, $submission);
        }
    }

    /**
     * Send email notification for form submission.
     *
     * @param array $data
     * @param array $formConfig
     * @param FormSubmission $submission
     * @return void
     */
    protected function sendEmailNotification(array $data, array $formConfig, FormSubmission $submission): void
    {
        try {
            $to = $formConfig['settings']['notify_email'];
            $subject = $formConfig['settings']['email_subject'] ?? 'New Form Submission: ' . $formConfig['name'];

            Mail::raw($this->formatEmailBody($data, $formConfig, $submission), function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('WLCMS Form Email Failed', [
                'submission_id' => $submission->id,
                'form' => $formConfig['identifier'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format email body from form data.
     *
     * @param array $data
     * @param array $formConfig
     * @param FormSubmission $submission
     * @return string
     */
    protected function formatEmailBody(array $data, array $formConfig, FormSubmission $submission): string
    {
        $body = "New submission for: {$formConfig['name']}\n\n";
        $body .= "Submission ID: {$submission->id}\n";
        $body .= "Submitted at: " . $submission->submitted_at->format('Y-m-d H:i:s') . "\n";
        $body .= "IP Address: {$submission->ip_address}\n\n";
        $body .= "Form Data:\n";
        $body .= str_repeat('-', 50) . "\n\n";

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $body .= ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
        }

        $body .= "\n" . str_repeat('-', 50) . "\n";
        $body .= "View in admin: " . route('wlcms.admin.form-submissions.show', $submission->id) . "\n";

        return $body;
    }

    /**
     * Return success response.
     *
     * @param array $formConfig
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function successResponse(array $formConfig, Request $request)
    {
        $message = $formConfig['success_message'] ?? 'Thank you for your submission!';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        $redirect = $formConfig['redirect_url'] ?? back()->getTargetUrl();

        return redirect($redirect)->with('success', $message);
    }

    /**
     * Return error response.
     *
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $code);
        }

        return back()
            ->withErrors(['form' => $message])
            ->withInput();
    }
}
