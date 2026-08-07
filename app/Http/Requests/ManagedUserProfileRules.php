<?php

namespace App\Http\Requests;

use App\Services\CountryCatalogService;
use Illuminate\Validation\Rule;

final class ManagedUserProfileRules
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalized(array $input): array
    {
        $nullable = [
            'phone',
            'address',
            'country_code',
            'city',
            'state',
            'birthday',
            'linkedin',
            'github',
            'x_url',
            'facebook',
            'instagram',
            'website',
        ];

        $out = [];
        foreach ($nullable as $key) {
            if (! array_key_exists($key, $input)) {
                continue;
            }
            $value = $input[$key];
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($key === 'country_code' && is_string($value) && $value !== '') {
                $value = strtoupper($value);
            }
            $out[$key] = $value === '' || $value === null ? null : $value;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public static function base(): array
    {
        $codes = app(CountryCatalogService::class)->allowedCodes();

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2', Rule::in($codes)],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'x_url' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }
}
