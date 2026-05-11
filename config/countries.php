<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public country catalog API
    |--------------------------------------------------------------------------
    |
    | Used when adding countries. Fetches alpha-2 codes, common English names,
    | and UN M.49 numeric codes (ccn3) for sort_order. Cached to avoid hammering
    | the remote service.
    |
    */

    'catalog_url' => env('COUNTRIES_CATALOG_URL', 'https://restcountries.com/v3.1/all?fields=cca2,name,ccn3'),

    'catalog_cache_ttl' => (int) env('COUNTRIES_CATALOG_CACHE_TTL', 60 * 24 * 7),

    'http_timeout' => (int) env('COUNTRIES_CATALOG_HTTP_TIMEOUT', 25),
];
