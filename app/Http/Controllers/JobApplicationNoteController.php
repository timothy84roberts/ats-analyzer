<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobApplicationNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobApplicationNoteController extends Controller
{
    public function store(Request $request, JobApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $body = trim($data['body']);
        if ($body === '') {
            return back()->withErrors(['body' => __('Note cannot be empty.')])->withInput();
        }

        JobApplicationNote::create([
            'job_application_id' => $application->id,
            'user_id' => $request->user()->id,
            'body' => $body,
        ]);

        return redirect()->route('applications.index')->with('status', 'Note added.');
    }

    public function destroy(JobApplication $application, JobApplicationNote $note): RedirectResponse
    {
        if ($note->job_application_id !== $application->id) {
            abort(404);
        }

        $note->delete();

        return redirect()->route('applications.show', $application)->with('status', __('Note deleted.'));
    }
}
