<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CountryCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CountryCatalogStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        app(CountryCatalogService::class)->clearCache();
    }

    public function test_store_creates_country_using_catalog_api(): void
    {
        Http::fake([
            'https://restcountries.com/v3.1/*' => Http::response([
                ['cca2' => 'TX', 'name' => ['common' => 'Testland'], 'ccn3' => '999'],
            ], 200),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('countries.store'), ['code' => 'TX'])
            ->assertRedirect(route('countries.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('countries', [
            'code' => 'TX',
            'name' => 'Testland',
            'sort_order' => 999,
        ]);
    }

    public function test_store_rejects_code_not_in_catalog(): void
    {
        Http::fake([
            'https://restcountries.com/v3.1/*' => Http::response([
                ['cca2' => 'AA', 'name' => ['common' => 'Alpha'], 'ccn3' => '001'],
            ], 200),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('countries.create'))
            ->post(route('countries.store'), ['code' => 'ZZ'])
            ->assertSessionHasErrors('code');
    }
}
