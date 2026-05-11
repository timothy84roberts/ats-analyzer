<?php

namespace Database\Factories;

use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->words(3, true));

        return [
            'slug' => $slug,
            'label' => fake()->sentence(3),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
