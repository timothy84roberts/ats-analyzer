<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtsLabAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_scan_page(): void
    {
        $this->get(route('ats-scanner.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_scan_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('ats-scanner.index'))
            ->assertOk();
    }
}
