<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->optional()->paragraph(),
            'outcome_status' => JobApplication::OUTCOME_WAITING,
            'pipeline_stage_id' => PipelineStage::factory(),
            'rejection_reason' => null,
            'country_id' => Country::factory(),
            'company_name' => fake()->company(),
            'platform_id' => Platform::factory(),
            'analysis_percentage' => null,
            'applied_on' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'meta' => null,
        ];
    }
}
