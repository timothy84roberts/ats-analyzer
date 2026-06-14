<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService;
    }

    public function test_week_period_shows_seven_daily_buckets_for_current_week(): void
    {
        Carbon::setTestNow('2026-06-15');

        $user = $this->seedUserWithApplication('2026-06-10');
        $this->seedApplication($user, '2026-06-12');
        $this->seedApplication($user, '2026-05-01');

        $data = $this->service->build($user, ['period' => 'week']);

        $this->assertCount(7, $data['timeSeriesLabels']);
        $this->assertSame('2026-06-15', $data['timeSeriesLabels'][0]);
        $this->assertSame('2026-06-21', $data['timeSeriesLabels'][6]);
        $this->assertSame(2, array_sum($data['timeSeriesValues']));
        $this->assertSame(0, $data['timeSeriesValues'][0]);
        $this->assertSame(1, $data['timeSeriesValues'][4]);

        Carbon::setTestNow();
    }

    public function test_month_period_shows_daily_buckets_for_current_month(): void
    {
        Carbon::setTestNow('2026-06-15');

        $user = $this->seedUserWithApplication('2026-06-01');
        $this->seedApplication($user, '2026-06-30');
        $this->seedApplication($user, '2026-05-31');

        $data = $this->service->build($user, ['period' => 'month']);

        $this->assertCount(30, $data['timeSeriesLabels']);
        $this->assertSame('2026-06-01', $data['timeSeriesLabels'][0]);
        $this->assertSame('2026-06-30', $data['timeSeriesLabels'][29]);
        $this->assertSame(2, array_sum($data['timeSeriesValues']));

        Carbon::setTestNow();
    }

    public function test_year_period_groups_counts_by_month(): void
    {
        Carbon::setTestNow('2026-06-15');

        $user = $this->seedUserWithApplication('2026-01-05');
        $this->seedApplication($user, '2026-03-12');
        $this->seedApplication($user, '2026-03-20');
        $this->seedApplication($user, '2025-12-31');

        $data = $this->service->build($user, ['period' => 'year']);

        $this->assertCount(12, $data['timeSeriesLabels']);
        $this->assertSame('Jan 2026', $data['timeSeriesLabels'][0]);
        $this->assertSame('Dec 2026', $data['timeSeriesLabels'][11]);
        $this->assertSame(1, $data['timeSeriesValues'][0]);
        $this->assertSame(2, $data['timeSeriesValues'][2]);
        $this->assertSame(3, array_sum($data['timeSeriesValues']));

        Carbon::setTestNow();
    }

    public function test_offset_navigates_to_previous_week(): void
    {
        Carbon::setTestNow('2026-06-15');

        $user = $this->seedUserWithApplication('2026-05-20');
        $this->seedApplication($user, '2026-06-10');

        $data = $this->service->build($user, ['period' => 'week', 'offset' => -1]);

        $this->assertSame('May 18 – 24, 2026', $data['periodLabel']);
        $this->assertSame(1, array_sum($data['timeSeriesValues']));

        Carbon::setTestNow();
    }

    public function test_offset_navigates_to_previous_month(): void
    {
        Carbon::setTestNow('2026-06-15');

        $user = $this->seedUserWithApplication('2026-05-10');
        $this->seedApplication($user, '2026-06-01');

        $data = $this->service->build($user, ['period' => 'month', 'offset' => -1]);

        $this->assertSame('May 2026', $data['periodLabel']);
        $this->assertSame(1, array_sum($data['timeSeriesValues']));

        Carbon::setTestNow();
    }

    public function test_offset_navigates_to_previous_year(): void
    {
        Carbon::setTestNow('2026-06-15');

        $user = $this->seedUserWithApplication('2025-07-01');
        $this->seedApplication($user, '2026-01-01');

        $data = $this->service->build($user, ['period' => 'year', 'offset' => -1]);

        $this->assertSame('2025', $data['periodLabel']);
        $this->assertSame(1, array_sum($data['timeSeriesValues']));

        Carbon::setTestNow();
    }

    private function seedUserWithApplication(string $appliedOn): User
    {
        $user = User::factory()->create();

        $this->seedApplication($user, $appliedOn);

        return $user;
    }

    private function seedApplication(User $user, string $appliedOn): void
    {
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        $stage = PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        JobApplication::factory()->create([
            'user_id' => $user->id,
            'country_id' => $country->id,
            'platform_id' => $platform->id,
            'pipeline_stage_id' => $stage->id,
            'applied_on' => $appliedOn,
        ]);
    }
}
