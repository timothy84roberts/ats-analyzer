<?php

namespace Tests\Unit;

use App\Services\FinancialPeriodService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class FinancialPeriodServiceTest extends TestCase
{
    private FinancialPeriodService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FinancialPeriodService;
    }

    public function test_auto_reporting_month_before_cutoff(): void
    {
        $this->assertSame('2026-05', $this->service->autoReportingMonth(Carbon::parse('2026-05-25')));
        $this->assertSame('2026-04', $this->service->autoReportingMonth(Carbon::parse('2026-04-25')));
    }

    public function test_auto_reporting_month_after_cutoff(): void
    {
        $this->assertSame('2026-06', $this->service->autoReportingMonth(Carbon::parse('2026-05-26')));
        $this->assertSame('2026-05', $this->service->autoReportingMonth(Carbon::parse('2026-04-26')));
    }

    public function test_period_bounds_for_may(): void
    {
        $bounds = $this->service->periodBounds('2026-05');

        $this->assertSame('2026-04-26', $bounds['start']->toDateString());
        $this->assertSame('2026-05-25', $bounds['end']->toDateString());
    }

    public function test_period_bounds_for_january_year_rollover(): void
    {
        $bounds = $this->service->periodBounds('2026-01');

        $this->assertSame('2025-12-26', $bounds['start']->toDateString());
        $this->assertSame('2026-01-25', $bounds['end']->toDateString());
    }

    public function test_resolve_with_override_after_cutoff(): void
    {
        $date = Carbon::parse('2026-05-28');

        $this->assertSame('2026-06', $this->service->resolveReportingMonth($date, false));
        $this->assertSame('2026-05', $this->service->resolveReportingMonth($date, true));
    }

    public function test_resolve_ignores_override_before_cutoff(): void
    {
        $date = Carbon::parse('2026-05-10');

        $this->assertSame('2026-05', $this->service->resolveReportingMonth($date, false));
        $this->assertSame('2026-05', $this->service->resolveReportingMonth($date, true));
    }

    public function test_has_manual_override(): void
    {
        $date = Carbon::parse('2026-05-28');

        $this->assertTrue($this->service->hasManualOverride($date, '2026-05'));
        $this->assertFalse($this->service->hasManualOverride($date, '2026-06'));
    }

    public function test_is_override_applicable(): void
    {
        $this->assertTrue($this->service->isOverrideApplicable(Carbon::parse('2026-05-26')));
        $this->assertFalse($this->service->isOverrideApplicable(Carbon::parse('2026-05-25')));
    }
}
