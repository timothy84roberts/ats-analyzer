<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class FinancialPeriodService
{
    public const PERIOD_END_DAY = 25;

    public function autoReportingMonth(Carbon $date): string
    {
        if ($date->day <= self::PERIOD_END_DAY) {
            return $date->format('Y-m');
        }

        return $date->copy()->addMonthNoOverflow()->format('Y-m');
    }

    /**
     * @return array{start: Carbon, end: Carbon}
     */
    public function periodBounds(string $yearMonth): array
    {
        $month = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();

        return [
            'start' => $month->copy()->subMonthNoOverflow()->day(self::PERIOD_END_DAY + 1)->startOfDay(),
            'end'   => $month->copy()->day(self::PERIOD_END_DAY)->endOfDay(),
        ];
    }

    public function currentReportingMonth(): string
    {
        return $this->autoReportingMonth(Carbon::now());
    }

    public function resolveReportingMonth(Carbon $date, bool $assignToPreviousMonth): string
    {
        $auto = $this->autoReportingMonth($date);

        if ($assignToPreviousMonth && $date->day > self::PERIOD_END_DAY) {
            return Carbon::createFromFormat('Y-m', $auto)
                ->subMonthNoOverflow()
                ->format('Y-m');
        }

        return $auto;
    }

    public function isOverrideApplicable(Carbon $date): bool
    {
        return $date->day > self::PERIOD_END_DAY;
    }

    public function hasManualOverride(Carbon $date, string $storedReportingMonth): bool
    {
        return $storedReportingMonth !== $this->autoReportingMonth($date);
    }

    public function previousReportingMonthLabel(string $yearMonth): string
    {
        return Carbon::createFromFormat('Y-m', $yearMonth)
            ->subMonthNoOverflow()
            ->format('F Y');
    }

    public function reportingMonthLabel(string $yearMonth): string
    {
        return Carbon::createFromFormat('Y-m', $yearMonth)->format('F Y');
    }
}
