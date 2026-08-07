<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_redirects_back_to_create_when_keep_continue_is_checked(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false, 'name' => 'Managed Keep User']);
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $appliedOn = now()->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('applications.store'), [
            'user_id' => $managed->id,
            'title' => 'Frontend Engineer',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Acme',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => $appliedOn,
            'keep_creating' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('applications.create'))
            ->assertSessionHas('_old_input.user_id', (string) $managed->id)
            ->assertSessionHas('_old_input.country_id', (string) $country->id)
            ->assertSessionHas('_old_input.platform_id', (string) $platform->id)
            ->assertSessionHas('_old_input.applied_on', $appliedOn)
            ->assertSessionHas('_old_input.keep_creating', '1');

        $this->actingAs($admin)
            ->withSession($response->getSession()->all())
            ->get(route('applications.create'))
            ->assertOk()
            ->assertSee('selected', false)
            ->assertSee((string) $managed->id, false)
            ->assertSee('Managed Keep User', false);
    }
}
