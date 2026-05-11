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
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'Frontend Engineer',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Acme',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
            'keep_creating' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.create'));
    }
}

