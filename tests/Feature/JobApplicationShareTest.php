<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobApplicationShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_posts_title_description_and_resume_to_share_webhook(): void
    {
        Storage::fake('local');
        Http::fake([
            'https://script.google.com/*' => Http::response(['ok' => true], 200),
        ]);

        config([
            'services.job_application_share.enabled' => true,
            'services.job_application_share.webhook_url' => 'https://script.google.com/macros/s/test/exec',
            'services.job_application_share.token' => 'secret-token',
            'services.job_application_share.folder_id' => '1jnLo3dJbuEPVkLId2ys2UBHXkcYG9Bhy',
            'services.job_application_share.timeout' => 30,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false, 'name' => 'Shared User']);
        $country = Country::factory()->create(['name' => 'United States']);
        $platform = Platform::factory()->create(['name' => 'LinkedIn']);
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $resume = UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf');

        $this->actingAs($admin)->post(route('applications.store'), [
            'user_id' => $managed->id,
            'title' => 'Staff iOS Engineer',
            'description' => '<p>About the job</p><p><br></p><p>We do Consulting Differently</p><p>Build great apps.</p>',
            'country_id' => $country->id,
            'company_name' => 'Acme',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
            'resume' => $resume,
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $application = JobApplication::query()->where('title', 'Staff iOS Engineer')->first();
        $this->assertNotNull($application);

        Http::assertSent(function ($request) use ($application) {
            if ($request->url() !== 'https://script.google.com/macros/s/test/exec') {
                return false;
            }

            $data = $request->data();

            return ($data['token'] ?? null) === 'secret-token'
                && ($data['folder_id'] ?? null) === '1jnLo3dJbuEPVkLId2ys2UBHXkcYG9Bhy'
                && ($data['application_id'] ?? null) === $application->id
                && ($data['title'] ?? null) === 'Staff iOS Engineer'
                && ($data['description_text'] ?? null) === "About the job\n\nWe do Consulting Differently\nBuild great apps."
                && isset($data['resume']['content_base64'])
                && ($data['resume']['mime_type'] ?? null) === 'application/pdf'
                && ($data['resume']['filename'] ?? null) === 'Shayne Guiliano_Acme.pdf';
        });
    }

    public function test_share_is_skipped_when_disabled(): void
    {
        Http::fake();
        config([
            'services.job_application_share.enabled' => false,
            'services.job_application_share.webhook_url' => 'https://script.google.com/macros/s/test/exec',
            'services.job_application_share.token' => 'secret-token',
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false]);
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($admin)->post(route('applications.store'), [
            'user_id' => $managed->id,
            'title' => 'No Share Role',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => null,
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        Http::assertNothingSent();
    }
}
