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
        config(['countries.catalog_api_key' => 'test-key']);
        app(CountryCatalogService::class)->clearCache();
    }

    /**
     * @param  list<array<string, mixed>>  $objects
     */
    private function fakeCatalogResponse(array $objects): void
    {
        Http::fake([
            'https://api.restcountries.com/countries/v5*' => Http::response([
                'data' => [
                    'objects' => $objects,
                    'meta' => [
                        'total' => count($objects),
                        'count' => count($objects),
                        'limit' => 100,
                        'offset' => 0,
                        'more' => false,
                    ],
                ],
            ], 200),
        ]);
    }

    public function test_store_creates_country_using_catalog_api(): void
    {
        $this->fakeCatalogResponse([
            [
                'codes' => ['alpha_2' => 'TX', 'ccn3' => '999'],
                'names' => ['common' => 'Testland'],
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
        $this->fakeCatalogResponse([
            [
                'codes' => ['alpha_2' => 'AA', 'ccn3' => '001'],
                'names' => ['common' => 'Alpha'],
            ],
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('countries.create'))
            ->post(route('countries.store'), ['code' => 'ZZ'])
            ->assertSessionHasErrors('code');
    }

    public function test_create_works_without_api_key_using_bundled_catalog(): void
    {
        config(['countries.catalog_api_key' => '']);
        app(CountryCatalogService::class)->clearCache();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('countries.create'))
            ->assertOk()
            ->assertViewHas('options');
    }

    public function test_create_shows_catalog_error_when_bundled_catalog_missing(): void
    {
        config(['countries.catalog_api_key' => '']);
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
}
