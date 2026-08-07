<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee(__('User'), false);
    }

    public function test_dashboard_outcome_cards_show_percent_of_total(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false]);
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $stage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        JobApplication::factory()->count(3)->create([
            'user_id' => $managed->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'outcome_status' => JobApplication::OUTCOME_WAITING,
            'applied_on' => now()->toDateString(),
        ]);
        JobApplication::factory()->create([
            'user_id' => $managed->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'outcome_status' => JobApplication::OUTCOME_REJECTED,
            'applied_on' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('75%', false)
            ->assertSee('25%', false);
    }
}
