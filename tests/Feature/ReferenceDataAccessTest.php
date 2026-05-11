<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceDataAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_countries_index(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->seed(\Database\Seeders\PipelineStageSeeder::class);
        $this->seed(\Database\Seeders\CountrySeeder::class);
        $this->seed(\Database\Seeders\PlatformSeeder::class);

        $this->actingAs($user)
            ->get(route('countries.index'))
            ->assertOk();
    }

    public function test_authenticated_admin_can_open_countries_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->seed(\Database\Seeders\PipelineStageSeeder::class);
        $this->seed(\Database\Seeders\CountrySeeder::class);
        $this->seed(\Database\Seeders\PlatformSeeder::class);

        $this->actingAs($admin)
            ->get(route('countries.index'))
            ->assertOk();
    }
}
