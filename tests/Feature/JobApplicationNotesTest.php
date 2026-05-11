<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_multiple_notes_to_application(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $defaultStage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'Backend engineer',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Acme',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $application = JobApplication::query()->where('title', 'Backend engineer')->first();
        $this->assertNotNull($application);
        $this->assertSame($user->id, $application->user_id);
        $this->assertSame($defaultStage->id, $application->pipeline_stage_id);

        $this->actingAs($user)->post(route('applications.notes.store', $application), [
            'body' => "Call: 2026-05-20 10:30\nSkill test due: Friday",
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $this->actingAs($user)->post(route('applications.notes.store', $application), [
            'body' => 'Follow up next Tuesday.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $this->assertDatabaseCount('job_application_notes', 2);
        $this->assertDatabaseHas('job_application_notes', [
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'body' => "Call: 2026-05-20 10:30\nSkill test due: Friday",
        ]);
        $this->assertDatabaseHas('job_application_notes', [
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'body' => 'Follow up next Tuesday.',
        ]);
    }
}

