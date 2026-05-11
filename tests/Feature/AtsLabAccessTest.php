<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AtsLabAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_ats_page_forbidden_when_module_disabled(): void
    {
        Config::set('ats.enabled', false);
        $user = User::factory()->create(['is_ats_lab_allowed' => true]);

        $this->actingAs($user)
            ->get(route('ats.index'))
            ->assertForbidden();
    }

    public function test_ats_page_forbidden_when_user_not_allowed(): void
    {
        Config::set('ats.enabled', true);
        $user = User::factory()->create(['is_ats_lab_allowed' => false]);

        $this->actingAs($user)
            ->get(route('ats.index'))
            ->assertForbidden();
    }

    public function test_ats_page_allowed_when_enabled_and_user_flagged(): void
    {
        Config::set('ats.enabled', true);
        $user = User::factory()->create(['is_ats_lab_allowed' => true]);

        $this->actingAs($user)
            ->get(route('ats.index'))
            ->assertOk();
    }
}
