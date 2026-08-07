<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobApplicationResumeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Country, 2: Platform, 3: PipelineStage}
     */
    private function seedUserAndRefs(): array
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $stage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        return [$user, $country, $platform, $stage];
    }

    public function test_store_saves_pdf_to_disk_and_database(): void
    {
        Storage::fake('local');
        [$user, $country, $platform, $_stage] = $this->seedUserAndRefs();

        $file = UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf');

        $this->actingAs($user)->post(route('applications.store'), [
            'user_id' => $user->id,
            'title' => 'Engineer',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => null,
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
            'resume' => $file,
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $application = JobApplication::query()->where('title', 'Engineer')->first();
        $this->assertNotNull($application);
        $this->assertNotNull($application->resume_path);
        Storage::disk('local')->assertExists($application->resume_path);
    }

    public function test_owner_can_stream_resume_and_preview_route_ok(): void
    {
        Storage::fake('local');
        [$user, $country, $platform, $stage] = $this->seedUserAndRefs();

        $path = 'job-resumes/'.$user->id.'/sample.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 minimal');

        $application = JobApplication::factory()->create([
            'user_id' => $user->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'resume_path' => $path,
        ]);

        $this->actingAs($user)
            ->get(route('applications.resume', $application))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_non_owner_can_stream_resume(): void
    {
        Storage::fake('local');
        [$owner, $country, $platform, $stage] = $this->seedUserAndRefs();
        $other = User::factory()->create();

        $path = 'job-resumes/'.$owner->id.'/sample.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4');

        $application = JobApplication::factory()->create([
            'user_id' => $owner->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'resume_path' => $path,
        ]);

        $this->actingAs($other)
            ->get(route('applications.resume', $application))
            ->assertOk();
    }

    public function test_update_remove_resume_clears_path_and_file(): void
    {
        Storage::fake('local');
        [$user, $country, $platform, $stage] = $this->seedUserAndRefs();

        $path = 'job-resumes/'.$user->id.'/old.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4');

        $application = JobApplication::factory()->create([
            'user_id' => $user->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'resume_path' => $path,
            'title' => 'Role',
        ]);

        $this->actingAs($user)->put(route('applications.update', $application), [
            'user_id' => $user->id,
            'title' => 'Role',
            'description' => null,
            'outcome_status' => $application->outcome_status,
            'rejection_reason' => null,
            'pipeline_stage_id' => $stage->id,
            'country_id' => $country->id,
            'company_name' => null,
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => $application->applied_on->format('Y-m-d'),
            'remove_resume' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.edit', $application));

        $application->refresh();
        $this->assertNull($application->resume_path);
        Storage::disk('local')->assertMissing($path);
    }
}
