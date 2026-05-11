<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_settings(): void
    {
        $this->get(route('settings.index'))->assertRedirect();
    }

    public function test_authenticated_user_can_view_settings_hub(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk();
    }

    public function test_admin_sees_settings_hub(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk();
    }
}
