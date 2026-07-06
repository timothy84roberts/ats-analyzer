<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CountryCatalogService
{
    private const CACHE_KEY = 'countries:catalog:v1';

    /**
     * Sorted by UN M.49 numeric code, then name.
     *
     * @return list<array{code: string, name: string, numeric_code: int}>
     */
    public function listSorted(): array
    {
        $ttl = max(60, (int) config('countries.catalog_cache_ttl', 604800));

        return Cache::remember(self::CACHE_KEY, $ttl, function (): array {
            return $this->loadCatalog();
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
    private function loadCatalog(): array
    {
        $apiKey = trim((string) config('countries.catalog_api_key', ''));
        if ($apiKey !== '') {
            try {
                return $this->fetchFromApi($apiKey);
            } catch (RuntimeException $e) {
                Log::warning('Country catalog API failed, using bundled list.', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $this->loadBundled();
    }

    /**
     * @return list<array{code: string, name: string, numeric_code: int}>
     */
    private function loadBundled(): array
    {
        $path = database_path('data/countries-catalog.json');
        if (! is_readable($path)) {
            throw new RuntimeException('Bundled country catalog is missing.');
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Bundled country catalog is invalid.');
        }

        $rows = [];
        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $code = isset($item['code']) && is_string($item['code']) ? strtoupper($item['code']) : '';
            $name = $item['name'] ?? null;
            if (strlen($code) !== 2 || ! is_string($name) || $name === '') {
                continue;
            }

            $rows[] = [
                'code' => $code,
                'name' => $name,
                'numeric_code' => max(0, (int) ($item['numeric_code'] ?? 0)),
            ];
        }

        if ($rows === []) {
            throw new RuntimeException('Bundled country catalog is empty.');
        }

        return $rows;
    }

    /**
     * @return list<array{code: string, name: string, numeric_code: int}>
     */
    private function fetchFromApi(string $apiKey): array
    {
        $baseUrl = rtrim((string) config('countries.catalog_url'), '/');
        $timeout = max(5, (int) config('countries.http_timeout', 25));
        $pageSize = min(100, max(1, (int) config('countries.catalog_page_size', 100)));

        $rows = [];
        $offset = 0;

        do {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withToken($apiKey)
                ->get($baseUrl, [
                    'response_fields' => 'names.common,codes.alpha_2,codes.ccn3',
                    'limit' => $pageSize,
                    'offset' => $offset,
                ]);

            if (! $response->successful()) {
                throw new RuntimeException('Country catalog HTTP '.$response->status());
            }

            /** @var mixed $decoded */
            $decoded = $response->json();
            if (! is_array($decoded)) {
                throw new RuntimeException('Country catalog invalid JSON');
            }

            if (($decoded['success'] ?? null) === false) {
                $message = $decoded['errors'][0]['message'] ?? 'Country catalog request failed.';

                throw new RuntimeException((string) $message);
            }

            $objects = $decoded['data']['objects'] ?? null;
            if (! is_array($objects)) {
                throw new RuntimeException('Country catalog response missing data.objects');
            }

            foreach ($objects as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $code = isset($item['codes']['alpha_2']) && is_string($item['codes']['alpha_2'])
                    ? strtoupper($item['codes']['alpha_2'])
                    : '';
                if (strlen($code) !== 2 || ! ctype_alpha($code)) {
                    continue;
                }

                $name = $item['names']['common'] ?? $item['names']['official'] ?? null;
                if (! is_string($name) || $name === '') {
                    continue;
                }

                $numericCode = $this->parseCcn3($item['codes']['ccn3'] ?? null);
                $rows[] = ['code' => $code, 'name' => $name, 'numeric_code' => $numericCode];
            }

            $more = (bool) ($decoded['data']['meta']['more'] ?? false);
            $offset += $pageSize;
        } while ($more);

        if ($rows === []) {
            throw new RuntimeException('Country catalog returned no countries.');
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
        Cache::forget('countries:catalog:restcountries:v5');
        Cache::forget('countries:catalog:restcountries:v2');
    }
}
