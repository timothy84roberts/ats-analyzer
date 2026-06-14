<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $user, array $input): array
    {
        $period = in_array($input['period'] ?? '', ['week', 'month', 'year'], true)
            ? $input['period']
            : 'week';
        $offset = (int) ($input['offset'] ?? 0);

        [$from, $to, $periodLabel] = $this->resolveRange($period, $offset);

        $base = JobApplication::query()->where('user_id', $user->id);
        if ($from && $to) {
            $base->whereBetween('applied_on', [$from, $to]);
        }

        if (! empty($input['country_id'])) {
            $base->where('country_id', (int) $input['country_id']);
        }
        if (! empty($input['platform_id'])) {
            $base->where('platform_id', (int) $input['platform_id']);
        }
        if (! empty($input['outcome_status']) && in_array($input['outcome_status'], JobApplication::outcomeStatuses(), true)) {
            $base->where('outcome_status', $input['outcome_status']);
        }

        $timeSeries = $this->timeSeries(clone $base, $period, $from, $to);
        $statusByCountry = $this->statusPivot(clone $base, 'countries', 'country_id', 'countries.name');
        $statusByPlatform = $this->statusPivot(clone $base, 'platforms', 'platform_id', 'platforms.name');
        $funnel = $this->funnel(clone $base);

        return [
            'period' => $period,
            'periodOffset' => $offset,
            'periodLabel' => $periodLabel,
            'from' => $from,
            'to' => $to,
            'timeSeriesLabels' => $timeSeries['labels'],
            'timeSeriesValues' => $timeSeries['values'],
            'statusByCountry' => $statusByCountry,
            'statusByPlatform' => $statusByPlatform,
            'funnelLabels' => $funnel['labels'],
            'funnelValues' => $funnel['values'],
            'totalsByOutcome' => (clone $base)
                ->selectRaw('outcome_status, COUNT(*) as c')
                ->groupBy('outcome_status')
                ->pluck('c', 'outcome_status')
                ->all(),
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolveRange(string $period, int $offset): array
    {
        $anchor = match ($period) {
            'week' => Carbon::now()->addWeeks($offset),
            'month' => Carbon::now()->addMonthsNoOverflow($offset),
            'year' => Carbon::now()->addYears($offset),
            default => Carbon::now()->addWeeks($offset),
        };

        [$from, $to] = match ($period) {
            'week' => [
                $anchor->copy()->startOfWeek(),
                $anchor->copy()->endOfWeek(),
            ],
            'month' => [
                $anchor->copy()->startOfMonth(),
                $anchor->copy()->endOfMonth(),
            ],
            'year' => [
                $anchor->copy()->startOfYear(),
                $anchor->copy()->endOfYear(),
            ],
            default => [
                $anchor->copy()->startOfWeek(),
                $anchor->copy()->endOfWeek(),
            ],
        };

        return [
            $from->toDateString(),
            $to->toDateString(),
            $this->periodLabel($period, $from, $to),
        ];
    }

    private function periodLabel(string $period, Carbon $from, Carbon $to): string
    {
        return match ($period) {
            'week' => $from->isSameMonth($to)
                ? $from->format('M j').' – '.$to->format('j, Y')
                : $from->format('M j').' – '.$to->format('M j, Y'),
            'month' => $from->format('F Y'),
            'year' => $from->format('Y'),
            default => $from->format('M j').' – '.$to->format('M j, Y'),
        };
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function timeSeries($query, string $period, string $from, string $to): array
    {
        $dates = (clone $query)->pluck('applied_on');

        if ($period === 'year') {
            return $this->buildMonthlyTimeSeries($dates, $from, $to);
        }

        return $this->buildDailyTimeSeries($dates, $from, $to);
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function buildDailyTimeSeries($dates, string $from, string $to): array
    {
        $counts = [];
        foreach ($dates as $appliedOn) {
            $key = Carbon::parse($appliedOn)->format('Y-m-d');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $labels = [];
        $values = [];
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $key;
            $values[] = $counts[$key] ?? 0;
            $cursor->addDay();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function buildMonthlyTimeSeries($dates, string $from, string $to): array
    {
        $counts = [];
        foreach ($dates as $appliedOn) {
            $key = Carbon::parse($appliedOn)->format('Y-m');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $labels = [];
        $values = [];
        $cursor = Carbon::parse($from)->startOfMonth();
        $end = Carbon::parse($to)->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $values[] = $counts[$key] ?? 0;
            $cursor->addMonth();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>}
     */
    private function statusPivot($query, string $joinTable, string $fkColumn, string $labelColumn): array
    {
        $rows = (clone $query)
            ->join($joinTable, "{$joinTable}.id", '=', "job_applications.{$fkColumn}")
            ->selectRaw("{$joinTable}.id as dim_id, {$labelColumn} as dim_label, job_applications.outcome_status, COUNT(*) as c")
            ->groupBy('dim_id', 'dim_label', 'job_applications.outcome_status')
            ->orderBy('dim_label')
            ->get();

        $dimensions = $rows->unique('dim_id')->sortBy('dim_label')->values();
        $outcomes = JobApplication::outcomeStatuses();
        $datasets = [];
        foreach ($outcomes as $outcome) {
            $data = [];
            foreach ($dimensions as $dim) {
                $data[] = (int) $rows
                    ->where('dim_id', $dim->dim_id)
                    ->where('outcome_status', $outcome)
                    ->sum('c');
            }
            $datasets[] = [
                'label' => $outcome,
                'data' => $data,
            ];
        }

        return [
            'labels' => $dimensions->pluck('dim_label')->all(),
            'datasets' => $datasets,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function funnel($query): array
    {
        $rows = (clone $query)
            ->join('pipeline_stages', 'pipeline_stages.id', '=', 'job_applications.pipeline_stage_id')
            ->selectRaw('pipeline_stages.label as lbl, COUNT(*) as c')
            ->groupBy('pipeline_stages.id', 'pipeline_stages.label', 'pipeline_stages.sort_order')
            ->orderBy('pipeline_stages.sort_order')
            ->get();

        return [
            'labels' => $rows->pluck('lbl')->all(),
            'values' => $rows->pluck('c')->map(fn ($c) => (int) $c)->all(),
        ];
    }
}
