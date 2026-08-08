<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public country catalog
    |--------------------------------------------------------------------------
    |
    | Primary source uses shorter common names (e.g. "United States") from the
    | world-countries dataset. Falls back to the bundled JSON if unreachable.
    | Optionally set COUNTRIES_CATALOG_API_KEY to prefer the REST Countries v5 API.
    |
    */

    'catalog_url' => env(
        'COUNTRIES_CATALOG_URL',
        'https://cdn.jsdelivr.net/npm/world-countries@5.0.0/countries.json'
    ),

    'catalog_api_key' => env('COUNTRIES_CATALOG_API_KEY'),

    'catalog_v5_url' => env('COUNTRIES_CATALOG_V5_URL', 'https://api.restcountries.com/countries/v5'),

    'catalog_cache_ttl' => (int) env('COUNTRIES_CATALOG_CACHE_TTL', 60 * 24 * 7),

    'catalog_page_size' => (int) env('COUNTRIES_CATALOG_PAGE_SIZE', 100),

    'http_timeout' => (int) env('COUNTRIES_CATALOG_HTTP_TIMEOUT', 25),
];
