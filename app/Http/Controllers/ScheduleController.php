<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobApplicationCall;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $calls = JobApplicationCall::query()
            ->with([
                'user:id,name',
                'application:id,title,company_name',
            ])
            ->orderBy('scheduled_at')
            ->get()
            ->map(function (JobApplicationCall $call) {
                return [
                    'id' => $call->id,
                    'title' => $call->title,
                    'description' => $call->description,
                    'start' => $call->scheduled_at?->toIso8601String(),
                    'user_id' => $call->user_id,
                    'user_name' => $call->user?->name,
                    'user_hue' => $call->user_id ? ($call->user_id * 47) % 360 : 0,
                    'application_id' => $call->application?->id,
                    'application_title' => $call->application?->title,
                    'company_name' => $call->application?->company_name,
                    'url' => $call->application
                        ? route('applications.show', $call->application).'#scheduled-calls'
                        : null,
                ];
            })
            ->values();

        return view('schedule.index', [
            'calls' => $calls,
            'managedUsers' => User::query()->where('is_admin', false)->orderBy('name')->get(),
            'applications' => JobApplication::query()
                ->with('user:id,name')
                ->orderBy('title')
                ->get(['id', 'title', 'company_name', 'user_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'scheduled_at' => ['required', 'date'],
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
            'job_application_id' => ['nullable', 'exists:job_applications,id'],
        ]);

        $title = trim($data['title']);
        if ($title === '') {
            return back()->withErrors(['title' => __('Title cannot be empty.')])->withInput();
        }

        $description = isset($data['description']) ? trim((string) $data['description']) : '';
        $description = $description === '' ? null : $description;

        JobApplicationCall::create([
            'job_application_id' => $data['job_application_id'] ?? null,
            'user_id' => $data['user_id'],
            'title' => $title,
            'description' => $description,
            'scheduled_at' => $data['scheduled_at'],
        ]);

        return redirect()->route('schedule.index')->with('status', __('Schedule item created.'));
    }
}
