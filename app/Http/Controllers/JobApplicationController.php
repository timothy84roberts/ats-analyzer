<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobApplicationRequest;
use App\Http\Requests\UpdateJobApplicationRequest;
use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JobApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(JobApplication::class, 'application');
    }

    public function index(Request $request): View
    {
        $query = JobApplication::query()
            ->with(['country', 'platform', 'pipelineStage'])
            ->where('user_id', $request->user()->id)
            ->when($request->filled('country_id'), fn ($q) => $q->where('country_id', $request->integer('country_id')))
            ->when($request->filled('platform_id'), fn ($q) => $q->where('platform_id', $request->integer('platform_id')))
            ->when(
                $request->filled('outcome_status') && in_array($request->input('outcome_status'), JobApplication::outcomeStatuses(), true),
                fn ($q) => $q->where('outcome_status', $request->input('outcome_status'))
            )
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->input('q').'%'))
            ->latest('applied_on');

        /** @var LengthAwarePaginator<int, JobApplication> $paginator */
        $paginator = $query->paginate(15);
        $applications = $paginator->withQueryString();

        return view('job-applications.index', [
            'applications' => $applications,
            'countries' => Country::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'platforms' => Platform::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'outcomeStatuses' => JobApplication::outcomeStatuses(),
        ]);
    }

    public function create(): View
    {
        return view('job-applications.create', $this->formOptions());
    }

    public function store(StoreJobApplicationRequest $request): RedirectResponse
    {
        $defaultStageId = PipelineStage::defaultIdForNewApplication();
        if ($defaultStageId === null) {
            throw ValidationException::withMessages([
                'title' => __('Add at least one pipeline stage before creating applications.'),
            ]);
        }

        $data = collect($request->validated())->except(['resume'])->all();
        $data['user_id'] = $request->user()->id;
        $data['outcome_status'] = JobApplication::OUTCOME_WAITING;
        $data['pipeline_stage_id'] = $defaultStageId;
        $data['rejection_reason'] = null;
        if ($request->hasFile('resume')) {
            $data['resume_path'] = $request->file('resume')->store(
                'job-resumes/'.$request->user()->id,
                'local'
            );
        }
        JobApplication::create($data);

        return redirect()->route('applications.index')->with('status', 'Application created.');
    }

    public function show(JobApplication $application): View
    {
        $application->load([
            'country',
            'platform',
            'pipelineStage',
            'stageHistories.pipelineStage',
            'notes.user',
        ]);

        return view('job-applications.show', compact('application'));
    }

    public function edit(JobApplication $application): View
    {
        return view('job-applications.edit', array_merge(
            ['application' => $application],
            $this->formOptions()
        ));
    }

    public function update(UpdateJobApplicationRequest $request, JobApplication $application): RedirectResponse
    {
        $data = collect($request->validated())->except(['resume', 'remove_resume'])->all();

        if ($request->hasFile('resume')) {
            $application->deleteResumeFromDisk();
            $data['resume_path'] = $request->file('resume')->store(
                'job-resumes/'.$request->user()->id,
                'local'
            );
        } elseif ($request->boolean('remove_resume')) {
            $application->deleteResumeFromDisk();
            $data['resume_path'] = null;
        }

        $application->update($data);

        return redirect()->route('applications.index')->with('status', 'Application updated.');
    }

    public function showResume(JobApplication $application): BinaryFileResponse
    {
        $this->authorize('view', $application);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (! $application->hasResume() || ! $disk->exists($application->resume_path)) {
            abort(404);
        }

        return response()->file($disk->path($application->resume_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="resume.pdf"',
        ]);
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        $application->delete();

        return redirect()->route('applications.index')->with('status', 'Application deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'platforms' => Platform::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'pipelineStages' => PipelineStage::query()->orderBy('sort_order')->get(),
            'outcomeOptions' => [
                JobApplication::OUTCOME_WAITING => __('Waiting'),
                JobApplication::OUTCOME_REJECTED => __('Rejected'),
                JobApplication::OUTCOME_INTERVIEW => __('Interview'),
                JobApplication::OUTCOME_SUCCESS => __('Success'),
            ],
        ];
    }
}
