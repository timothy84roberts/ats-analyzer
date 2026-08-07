<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobApplicationCall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobApplicationCallController extends Controller
{
    public function store(Request $request, JobApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'scheduled_at' => ['required', 'date'],
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
        ]);

        $title = trim($data['title']);
        if ($title === '') {
            return back()->withErrors(['title' => __('Title cannot be empty.')])->withInput();
        }

        $description = isset($data['description']) ? trim((string) $data['description']) : '';
        $description = $description === '' ? null : $description;

        JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $data['user_id'],
            'title' => $title,
            'description' => $description,
            'scheduled_at' => $data['scheduled_at'],
        ]);

        return redirect()->route('applications.index')->with('status', 'Call booked.');
    }

    public function destroy(JobApplication $application, JobApplicationCall $call): RedirectResponse
    {
        if ($call->job_application_id !== $application->id) {
            abort(404);
        }

        $call->delete();

        return redirect()->route('applications.show', $application)->with('status', __('Call deleted.'));
    }
}
