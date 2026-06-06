<?php

namespace Tests\Feature;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_reorder_platforms(): void
    {
        $user = User::factory()->create();
        $first = Platform::factory()->create(['name' => 'Alpha', 'slug' => 'alpha', 'sort_order' => 10]);
        $second = Platform::factory()->create(['name' => 'Beta', 'slug' => 'beta', 'sort_order' => 20]);
        $third = Platform::factory()->create(['name' => 'Gamma', 'slug' => 'gamma', 'sort_order' => 30]);

        $this->actingAs($user)
            ->postJson(route('platforms.reorder'), [
                'order' => [$third->id, $first->id, $second->id],
            ])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame(10, $third->fresh()->sort_order);
        $this->assertSame(20, $first->fresh()->sort_order);
        $this->assertSame(30, $second->fresh()->sort_order);
    }

    public function test_reorder_requires_valid_platform_ids(): void
    {
        $user = User::factory()->create();
        Platform::factory()->create(['slug' => 'alpha']);

        $this->actingAs($user)
            ->postJson(route('platforms.reorder'), [
                'order' => [999],
            ])
            ->assertUnprocessable();
    }

    public function test_guest_cannot_reorder_platforms(): void
    {
        $platform = Platform::factory()->create(['slug' => 'alpha']);

        $this->postJson(route('platforms.reorder'), [
            'order' => [$platform->id],
        ])->assertUnauthorized();
    }
}
