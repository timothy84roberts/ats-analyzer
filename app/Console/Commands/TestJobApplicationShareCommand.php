<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Services\JobApplicationShareService;
use Illuminate\Console\Command;

class TestJobApplicationShareCommand extends Command
{
    protected $signature = 'job-application:test-share
                            {application? : Existing application ID to re-share}
                            {--probe-only : Only check that the Apps Script webhook is alive}';

    protected $description = 'Probe the Google Apps Script webhook and/or re-share a job application into Drive folder "Job share"';

    public function handle(JobApplicationShareService $shareService): int
    {
        $this->line('enabled: '.(config('services.job_application_share.enabled') ? 'yes' : 'no'));
        $this->line('webhook: '.(config('services.job_application_share.webhook_url') ?: '(empty)'));
        $this->line('token: '.(config('services.job_application_share.token') ?: '(empty)'));
        $this->line('folder: '.(config('services.job_application_share.folder_id') ?: JobApplicationShareService::DEFAULT_FOLDER_ID).' ('.JobApplicationShareService::DEFAULT_FOLDER_NAME.')');
        $this->newLine();

        $probe = $shareService->probeWebhook();
        if ($probe['ok']) {
            $this->info('Probe OK (HTTP '.$probe['status'].'): '.$probe['message']);
        } else {
            $this->error('Probe FAILED (HTTP '.$probe['status'].'): '.$probe['message']);
            $this->line('Fix Apps Script first — Laravel cannot upload until /exec returns JSON.');

            return self::FAILURE;
        }

        if ($this->option('probe-only')) {
            return self::SUCCESS;
        }

        $this->newLine();

        $id = $this->argument('application');
        $application = $id
            ? JobApplication::query()->with(['user', 'country', 'platform'])->find($id)
            : JobApplication::query()->with(['user', 'country', 'platform'])->latest('id')->first();

        if (! $application) {
            $this->error('No job application found to share.');

            return self::FAILURE;
        }

        $this->info('Sharing application #'.$application->id.' — '.$application->title);

        $ok = $shareService->share($application);

        if ($ok) {
            $this->info('Share succeeded. Check Drive folder "Job share":');
            $this->line('https://drive.google.com/drive/folders/'.JobApplicationShareService::DEFAULT_FOLDER_ID);

            return self::SUCCESS;
        }

        $this->error('Share failed. Check storage/logs/laravel.log for details.');

        return self::FAILURE;
    }
}
