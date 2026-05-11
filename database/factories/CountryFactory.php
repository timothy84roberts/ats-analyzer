<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->lexify('??'));

        return [
            'name' => fake()->country(),
            'code' => $code,
            'is_active' => true,
            // Synthetic UN-style numeric derived from code (factories do not call REST API).
            'sort_order' => min(99_999, (ord($code[0]) * 1000) + ord($code[1])),
        ];
    }
}
