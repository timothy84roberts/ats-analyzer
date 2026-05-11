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
        $period = in_array($input['period'] ?? '', ['day', 'week', 'month', 'year'], true)
            ? $input['period']
            : 'month';

        [$from, $to] = $this->resolveRange($period, $input['from'] ?? null, $input['to'] ?? null);

        $base = JobApplication::query()
            ->where('user_id', $user->id)
            ->whereBetween('applied_on', [$from, $to]);

        if (! empty($input['country_id'])) {
            $base->where('country_id', (int) $input['country_id']);
        }
        if (! empty($input['platform_id'])) {
            $base->where('platform_id', (int) $input['platform_id']);
        }
        if (! empty($input['outcome_status']) && in_array($input['outcome_status'], JobApplication::outcomeStatuses(), true)) {
            $base->where('outcome_status', $input['outcome_status']);
        }

        $timeSeries = $this->timeSeries(clone $base, $period);
        $statusByCountry = $this->statusPivot(clone $base, 'countries', 'country_id', 'countries.name');
        $statusByPlatform = $this->statusPivot(clone $base, 'platforms', 'platform_id', 'platforms.name');
        $funnel = $this->funnel(clone $base);

        return [
            'period' => $period,
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
     * @return array{0: string, 1: string}
     */
    private function resolveRange(string $period, ?string $from, ?string $to): array
    {
        $toDate = $to ? Carbon::parse($to)->toDateString() : Carbon::now()->toDateString();

        if ($from) {
            return [Carbon::parse($from)->toDateString(), $toDate];
        }

        $end = Carbon::parse($toDate);

        $start = match ($period) {
            'day' => $end->copy()->subDays(29),
            'week' => $end->copy()->subWeeks(11)->startOfWeek(),
            'month' => $end->copy()->subMonths(11)->startOfMonth(),
            'year' => $end->copy()->subYears(4)->startOfYear(),
            default => $end->copy()->subMonths(11)->startOfMonth(),
        };

        return [$start->toDateString(), $toDate];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function timeSeries($query, string $period): array
    {
        $dates = (clone $query)->pluck('applied_on');

        $bucketKey = function (Carbon $d) use ($period): string {
            return match ($period) {
                'day' => $d->format('Y-m-d'),
                'week' => $d->format('o').'-W'.str_pad((string) $d->format('W'), 2, '0', STR_PAD_LEFT),
                'month' => $d->format('Y-m'),
                'year' => $d->format('Y'),
                default => $d->format('Y-m'),
            };
        };

        $counts = [];
        foreach ($dates as $appliedOn) {
            $d = Carbon::parse($appliedOn);
            $k = $bucketKey($d);
            $counts[$k] = ($counts[$k] ?? 0) + 1;
        }
        ksort($counts);

        return [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
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
