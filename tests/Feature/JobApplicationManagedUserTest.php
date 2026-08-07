<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationManagedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_requires_managed_user_and_rejects_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false, 'name' => 'Managed Applicant']);
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($admin)->post(route('applications.store'), [
            'user_id' => $admin->id,
            'title' => 'Should fail',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Acme',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasErrors('user_id');

        $this->actingAs($admin)->post(route('applications.store'), [
            'user_id' => $managed->id,
            'title' => 'Assigned role',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Acme',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $this->assertDatabaseHas('job_applications', [
            'title' => 'Assigned role',
            'user_id' => $managed->id,
        ]);
    }

    public function test_index_filters_by_user_and_shows_user_name(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $alice = User::factory()->create(['is_admin' => false, 'name' => 'Alice Managed']);
        $bob = User::factory()->create(['is_admin' => false, 'name' => 'Bob Managed']);
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $stage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        JobApplication::factory()->create([
            'user_id' => $alice->id,
            'title' => 'Alice Job',
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
        ]);
        JobApplication::factory()->create([
            'user_id' => $bob->id,
            'title' => 'Bob Job',
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
        ]);

        $this->actingAs($admin)
            ->get(route('applications.index', ['user_id' => $alice->id]))
            ->assertOk()
            ->assertSee('Alice Job')
            ->assertSee('Alice Managed')
            ->assertDontSee('Bob Job');
    }

    public function test_dashboard_aggregates_all_users_by_default_and_filters_optionally(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $alice = User::factory()->create(['is_admin' => false, 'name' => 'Alice Managed']);
        $bob = User::factory()->create(['is_admin' => false, 'name' => 'Bob Managed']);
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $stage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        JobApplication::factory()->count(2)->create([
            'user_id' => $alice->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'outcome_status' => JobApplication::OUTCOME_WAITING,
            'applied_on' => now()->toDateString(),
        ]);
        JobApplication::factory()->create([
            'user_id' => $bob->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'outcome_status' => JobApplication::OUTCOME_REJECTED,
            'applied_on' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard', ['period' => 'week']))
            ->assertOk()
            ->assertSee('Alice Managed')
            ->assertSee('Bob Managed')
            ->assertSee('66.7%', false)
            ->assertSee('33.3%', false);

        $this->actingAs($admin)
            ->get(route('dashboard', ['period' => 'week', 'user_id' => $alice->id]))
            ->assertOk()
            ->assertSee('100%', false)
            ->assertDontSee('33.3%', false);
    }
}
