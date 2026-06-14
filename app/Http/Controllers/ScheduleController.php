<?php

namespace App\Http\Controllers;

use App\Models\JobApplicationCall;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $calls = JobApplicationCall::query()
            ->whereHas('application', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['application:id,title,company_name'])
            ->orderBy('scheduled_at')
            ->get()
            ->map(function (JobApplicationCall $call) {
                return [
                    'id' => $call->id,
                    'title' => $call->title,
                    'description' => $call->description,
                    'start' => $call->scheduled_at?->toIso8601String(),
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
        ]);
    }
}
