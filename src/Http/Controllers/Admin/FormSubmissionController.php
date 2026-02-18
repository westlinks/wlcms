<?php

namespace Westlinks\Wlcms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Westlinks\Wlcms\Models\FormSubmission;
use Westlinks\Wlcms\Services\FormRegistry;

class FormSubmissionController extends Controller
{
    /**
     * Display a listing of form submissions.
     */
    public function index(Request $request)
    {
        $query = FormSubmission::query()->orderBy('submitted_at', 'desc');

        // Filter by form
        if ($request->filled('form')) {
            $query->forForm($request->form);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search in data
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('form_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereRaw("JSON_SEARCH(data, 'one', ?) IS NOT NULL", ["%{$search}%"]);
            });
        }

        $submissions = $query->paginate(20);
        
        // Get unique form identifiers for filter
        $forms = FormSubmission::select('form_identifier', 'form_name')
            ->distinct()
            ->get()
            ->pluck('form_name', 'form_identifier');

        return view('wlcms::admin.form-submissions.index', compact('submissions', 'forms'));
    }

    /**
     * Display the specified form submission.
     */
    public function show(FormSubmission $submission)
    {
        // Mark as read when viewing
        if ($submission->isUnread()) {
            $submission->markAsRead();
        }

        return view('wlcms::admin.form-submissions.show', compact('submission'));
    }

    /**
     * Update the status of a form submission.
     */
    public function updateStatus(Request $request, FormSubmission $submission)
    {
        $request->validate([
            'status' => 'required|in:unread,read,archived',
        ]);

        $submission->update(['status' => $request->status]);

        return back()->with('success', 'Submission status updated successfully.');
    }

    /**
     * Delete the specified form submission.
     */
    public function destroy(FormSubmission $submission)
    {
        $submission->delete();

        return redirect()->route('wlcms.admin.form-submissions.index')
            ->with('success', 'Submission deleted successfully.');
    }

    /**
     * Export form submissions to CSV.
     */
    public function export(Request $request)
    {
        $query = FormSubmission::query()->orderBy('submitted_at', 'desc');

        if ($request->filled('form')) {
            $query->forForm($request->form);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->get();

        $filename = 'form-submissions-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($submissions) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['ID', 'Form', 'Submitted At', 'Status', 'IP Address', 'Data']);

            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->id,
                    $submission->form_name,
                    $submission->submitted_at->format('Y-m-d H:i:s'),
                    $submission->status,
                    $submission->ip_address,
                    json_encode($submission->data),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
