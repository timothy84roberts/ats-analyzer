<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['slug' => PipelineStage::SLUG_RESUME_SUBMITTED, 'label' => 'Resume submit', 'sort_order' => 10],
            ['slug' => 'skill_test', 'label' => 'Skill test', 'sort_order' => 20],
            ['slug' => 'recruiter_interview', 'label' => 'Recruiter interview', 'sort_order' => 30],
            ['slug' => 'technical_interview', 'label' => 'Technical interview', 'sort_order' => 40],
            ['slug' => 'executive_hr_interview', 'label' => 'CEO / HR interview', 'sort_order' => 50],
            ['slug' => 'offer', 'label' => 'Offer', 'sort_order' => 60],
        ];

        foreach ($rows as $row) {
            DB::table('pipeline_stages')->updateOrInsert(
                ['slug' => $row['slug']],
                array_merge($row, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
