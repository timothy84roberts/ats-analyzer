<?php

namespace App\Observers;

use App\Models\JobApplication;
use App\Models\JobApplicationStageHistory;

class JobApplicationObserver
{
    public function created(JobApplication $jobApplication): void
    {
        JobApplicationStageHistory::create([
            'job_application_id' => $jobApplication->id,
            'pipeline_stage_id' => $jobApplication->pipeline_stage_id,
            'entered_at' => now(),
        ]);
    }

    public function updated(JobApplication $jobApplication): void
    {
        if ($jobApplication->wasChanged('pipeline_stage_id')) {
            JobApplicationStageHistory::create([
                'job_application_id' => $jobApplication->id,
                'pipeline_stage_id' => $jobApplication->pipeline_stage_id,
                'entered_at' => now(),
            ]);
        }
    }

    public function deleting(JobApplication $jobApplication): void
    {
        $jobApplication->deleteResumeFromDisk();
    }
}
