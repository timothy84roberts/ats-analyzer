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
        config([
            'countries.catalog_api_key' => '',
            'countries.catalog_url' => 'https://cdn.jsdelivr.net/npm/world-countries@5.0.0/countries.json',
        ]);
        app(CountryCatalogService::class)->clearCache();
    }

    /**
     * @param  list<array<string, mixed>>  $countries
     */
    private function fakePublicCatalog(array $countries): void
    {
        Http::fake([
            'cdn.jsdelivr.net/*' => Http::response($countries, 200),
        ]);
    }

    public function test_store_creates_country_using_public_catalog(): void
    {
        $this->fakePublicCatalog([
            [
                'cca2' => 'TX',
                'ccn3' => '999',
                'name' => ['common' => 'Testland', 'official' => 'Republic of Testland'],
            ],
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
        $this->fakePublicCatalog([
            [
                'cca2' => 'AA',
                'ccn3' => '001',
                'name' => ['common' => 'Alpha'],
            ],
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('countries.create'))
            ->post(route('countries.store'), ['code' => 'ZZ'])
            ->assertSessionHasErrors('code');
    }

    public function test_create_works_with_bundled_catalog_when_remote_fails(): void
    {
        Http::fake([
            'cdn.jsdelivr.net/*' => Http::response('unavailable', 503),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('countries.create'))
            ->assertOk()
            ->assertViewHas('options', function ($options) {
                $us = collect($options)->firstWhere('code', 'US');

                return $us !== null && $us['name'] === 'United States';
            });
    }

    public function test_create_shows_catalog_error_when_bundled_catalog_missing(): void
    {
        Http::fake([
            'cdn.jsdelivr.net/*' => Http::response('unavailable', 503),
        ]);
        app(CountryCatalogService::class)->clearCache();

        $path = database_path('data/countries-catalog.json');
        $backup = $path.'.bak';
        if (file_exists($path)) {
            rename($path, $backup);
        }

        try {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->get(route('countries.create'))
                ->assertRedirect(route('countries.index'))
                ->assertSessionHasErrors('catalog');
        } finally {
            if (file_exists($backup)) {
                rename($backup, $path);
            }
        }
    }

    public function test_catalog_prefers_common_short_names(): void
    {
        Http::fake([
            'cdn.jsdelivr.net/*' => Http::response('unavailable', 503),
        ]);
        app(CountryCatalogService::class)->clearCache();

        $name = app(CountryCatalogService::class)->nameForCode('US');

        $this->assertSame('United States', $name);
    }
}
