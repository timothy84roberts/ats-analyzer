<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CountryCatalogService
{
    private const CACHE_KEY = 'countries:catalog:restcountries:v2';

    /**
     * Sorted by UN M.49 numeric code, then name.
     *
     * @return list<array{code: string, name: string, numeric_code: int}>
     */
    public function listSorted(): array
    {
        $ttl = max(60, (int) config('countries.catalog_cache_ttl', 604800));

        return Cache::remember(self::CACHE_KEY, $ttl, function (): array {
            return $this->fetchAndNormalize();
        });
    }

    /**
     * @return list<string>
     */
    public function allowedCodes(): array
    {
        return array_values(array_unique(array_map(
            fn (array $row): string => $row['code'],
            $this->listSorted()
        )));
    }

    public function nameForCode(string $code): ?string
    {
        $code = strtoupper($code);
        foreach ($this->listSorted() as $row) {
            if ($row['code'] === $code) {
                return $row['name'];
            }
        }

        return null;
    }

    /**
     * UN M.49 numeric country code for sort_order (0 if unknown in catalog).
     */
    public function numericSortOrderForCode(string $code): int
    {
        $code = strtoupper($code);
        foreach ($this->listSorted() as $row) {
            if ($row['code'] === $code) {
                return $row['numeric_code'];
            }
        }

        return 0;
    }

    /**
     * @return list<array{code: string, name: string, numeric_code: int}>
     */
    private function fetchAndNormalize(): array
    {
        $url = (string) config('countries.catalog_url');
        $timeout = max(5, (int) config('countries.http_timeout', 25));

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Country catalog HTTP '.$response->status());
        }

        /** @var mixed $decoded */
        $decoded = $response->json();
        if (! is_array($decoded)) {
            throw new RuntimeException('Country catalog invalid JSON');
        }

        $rows = [];
        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }
            $code = isset($item['cca2']) && is_string($item['cca2']) ? strtoupper($item['cca2']) : '';
            if (strlen($code) !== 2 || ! ctype_alpha($code)) {
                continue;
            }
            $name = $item['name']['common'] ?? $item['name']['official'] ?? null;
            if (! is_string($name) || $name === '') {
                continue;
            }
            $numericCode = $this->parseCcn3($item['ccn3'] ?? null);
            $rows[] = ['code' => $code, 'name' => $name, 'numeric_code' => $numericCode];
        }

        usort($rows, function (array $a, array $b): int {
            $cmp = $a['numeric_code'] <=> $b['numeric_code'];
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp($a['name'], $b['name']);
        });

        return $rows;
    }

    private function parseCcn3(mixed $ccn3): int
    {
        if (is_int($ccn3)) {
            return max(0, $ccn3);
        }
        if (is_string($ccn3) && ctype_digit($ccn3)) {
            return (int) $ccn3;
        }

        return 0;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
