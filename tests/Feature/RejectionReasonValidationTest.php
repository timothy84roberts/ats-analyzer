<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RejectionReasonValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejection_reason_required_when_outcome_is_rejected_on_update(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $stage = PipelineStage::factory()->create();

        $application = JobApplication::factory()->create([
            'user_id' => $user->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'outcome_status' => JobApplication::OUTCOME_WAITING,
            'rejection_reason' => null,
        ]);

        $response = $this->actingAs($user)->from(route('applications.edit', $application))->put(route('applications.update', $application), [
            'user_id' => $user->id,
            'title' => $application->title,
            'description' => $application->description,
            'outcome_status' => JobApplication::OUTCOME_REJECTED,
            'rejection_reason' => null,
            'pipeline_stage_id' => $stage->id,
            'country_id' => $country->id,
            'company_name' => $application->company_name,
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => $application->applied_on->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_store_sets_waiting_and_default_stage_ignoring_client_outcome(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $defaultStage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);
        PipelineStage::factory()->create(['slug' => 'later', 'sort_order' => 90]);

        $response = $this->actingAs($user)->post(route('applications.store'), [
            'user_id' => $user->id,
            'title' => 'Backend engineer',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Acme',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
            'outcome_status' => JobApplication::OUTCOME_REJECTED,
            'pipeline_stage_id' => PipelineStage::orderByDesc('id')->value('id'),
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $application = JobApplication::query()->where('title', 'Backend engineer')->first();
        $this->assertNotNull($application);
        $this->assertSame(JobApplication::OUTCOME_WAITING, $application->outcome_status);
        $this->assertSame($defaultStage->id, $application->pipeline_stage_id);
        $this->assertNull($application->rejection_reason);
    }
}
