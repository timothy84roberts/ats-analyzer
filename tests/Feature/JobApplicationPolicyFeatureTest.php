<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationPolicyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_application(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $application = JobApplication::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->get(route('applications.show', $application))
            ->assertForbidden();
    }

    public function test_owner_can_view_own_application(): void
    {
        $owner = User::factory()->create();
        $application = JobApplication::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->get(route('applications.show', $application))
            ->assertOk();
    }
}
