<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public country catalog API
    |--------------------------------------------------------------------------
    |
    | Used when adding countries. By default a bundled ISO 3166 list is used.
    | Set COUNTRIES_CATALOG_API_KEY to refresh from the REST Countries v5 API.
    |
    */

    'catalog_url' => env('COUNTRIES_CATALOG_URL', 'https://api.restcountries.com/countries/v5'),

    'catalog_api_key' => env('COUNTRIES_CATALOG_API_KEY'),

    'catalog_cache_ttl' => (int) env('COUNTRIES_CATALOG_CACHE_TTL', 60 * 24 * 7),

    'catalog_page_size' => (int) env('COUNTRIES_CATALOG_PAGE_SIZE', 100),

    'http_timeout' => (int) env('COUNTRIES_CATALOG_HTTP_TIMEOUT', 25),
];
