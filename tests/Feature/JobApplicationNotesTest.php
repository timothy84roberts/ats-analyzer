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

    public function test_store_saves_notes(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $defaultStage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'Backend engineer',
            'description' => null,
            'notes' => "Call: 2026-05-20 10:30\nSkill test due: Friday",
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
        $this->assertSame("Call: 2026-05-20 10:30\nSkill test due: Friday", $application->notes);
    }
}

