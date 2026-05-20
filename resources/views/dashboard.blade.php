<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Dashboard') }}</h1>
    </x-slot>

    <div class="admin-card admin-card--pad">
        <form method="get" action="{{ route('dashboard') }}" class="admin-toolbar" x-data="{ useDate: @js((bool) ($useDate ?? false)) }">
            <div class="admin-field">
                <span class="admin-label">{{ __('Period') }}</span>
                <select name="period" class="admin-select">
                    @foreach (['day' => __('By day'), 'week' => __('By week'), 'month' => __('By month'), 'year' => __('By year')] as $val => $label)
                        <option value="{{ $val }}" @selected(($period ?? 'day') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Country') }}</span>
                <select name="country_id" class="admin-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterCountries as $c)
                        <option value="{{ $c->id }}" @selected((string) request('country_id') === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Platform') }}</span>
                <select name="platform_id" class="admin-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterPlatforms as $p)
                        <option value="{{ $p->id }}" @selected((string) request('platform_id') === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Outcome') }}</span>
                <select name="outcome_status" class="admin-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($outcomeOptions as $o)
                        <option value="{{ $o }}" @selected(request('outcome_status') === $o)>{{ ucfirst($o) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Date option') }}</span>
                <input type="hidden" name="use_date" value="0" :disabled="useDate">
                <label class="admin-check">
                    <input type="checkbox" name="use_date" value="1" x-model="useDate">
                    <span>{{ __('Use date range') }}</span>
                </label>
            </div>
            <div class="admin-field" x-show="useDate" x-cloak>
                <span class="admin-label">{{ __('From') }}</span>
                <input type="date" name="from" value="{{ $from ?? '' }}" class="admin-input" :disabled="!useDate">
            </div>
            <div class="admin-field" x-show="useDate" x-cloak>
                <span class="admin-label">{{ __('To') }}</span>
                <input type="date" name="to" value="{{ $to ?? '' }}" class="admin-input" :disabled="!useDate">
            </div>
            <div class="admin-field" style="align-self: flex-end;">
                <span class="admin-label" style="opacity:0;">&nbsp;</span>
                <button type="submit" class="admin-btn admin-btn--primary">{{ __('Apply') }}</button>
            </div>
        </form>
    </div>

    @php
        $outcomeStatuses = \App\Models\JobApplication::outcomeStatuses();
        $outcomeStatTotal = collect($outcomeStatuses)->sum(fn (string $status) => (int) ($totalsByOutcome[$status] ?? 0));
    @endphp

    <div class="admin-stat-grid">
        @foreach (\App\Models\JobApplication::outcomeStatPresentation() as $o => $meta)
            @php
                $outcomeCount = (int) ($totalsByOutcome[$o] ?? 0);
                $outcomePercent = $outcomeStatTotal > 0
                    ? round($outcomeCount / $outcomeStatTotal * 100, 1)
                    : 0.0;
            @endphp
            <div class="admin-stat">
                <div class="admin-stat__label">{{ ucfirst($o) }}</div>
                <div class="admin-stat__value-row">
                    <span class="admin-stat__value">{{ $outcomeCount }}</span>
                    <span class="admin-stat__pct">{{ $outcomePercent }}%</span>
                </div>
                <div class="admin-stat__icon admin-stat__icon--{{ $meta['icon'] }}">
                    @if ($o === \App\Models\JobApplication::OUTCOME_WAITING)
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($o === \App\Models\JobApplication::OUTCOME_REJECTED)
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @elseif($o === \App\Models\JobApplication::OUTCOME_INTERVIEW)
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    @else
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="admin-card">
        <div class="admin-chart-head">{{ __('Applications over time') }}</div>
        <div class="admin-chart-body">
            <canvas id="chartTime" height="100"></canvas>
        </div>
    </div>

    <div class="admin-grid-2">
        <div class="admin-card">
            <div class="admin-chart-head">{{ __('Outcome by country') }}</div>
            <div class="admin-chart-body">
                <canvas id="chartCountry" height="160"></canvas>
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-chart-head">{{ __('Outcome by platform') }}</div>
            <div class="admin-chart-body">
                <canvas id="chartPlatform" height="160"></canvas>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-chart-head">{{ __('Pipeline stage distribution') }}</div>
        <div class="admin-chart-body">
            <canvas id="chartFunnel" height="100"></canvas>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const accent = getComputedStyle(document.documentElement).getPropertyValue('--admin-accent').trim() || '#2ea884';
            const outcomeColors = {
                waiting: 'rgba(245, 158, 11, 0.45)',   // warning (light)
                rejected: 'rgba(239, 68, 68, 0.45)',   // danger (light)
                interview: 'rgba(13, 110, 253, 0.45)', // primary (light)
                success: 'rgba(34, 197, 94, 0.45)',    // success (light)
            };
            const timeLabels = @json($timeSeriesLabels ?? []);
            const timeValues = @json($timeSeriesValues ?? []);
            const countryChart = @json($statusByCountry ?? ['labels' => [], 'datasets' => []]);
            const platformChart = @json($statusByPlatform ?? ['labels' => [], 'datasets' => []]);
            const funnelLabels = @json($funnelLabels ?? []);
            const funnelValues = @json($funnelValues ?? []);

            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Chart === 'undefined') return;
                if (timeLabels.length) {
                    new Chart(document.getElementById('chartTime'), {
                        type: 'line',
                        data: { labels: timeLabels, datasets: [{ label: @json(__('Applications')), data: timeValues, borderColor: accent, backgroundColor: accent + '22', fill: true, tension: 0.25 }] },
                        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
                    });
                }
                if (countryChart.labels && countryChart.labels.length) {
                    new Chart(document.getElementById('chartCountry'), {
                        type: 'bar',
                        data: { labels: countryChart.labels, datasets: countryChart.datasets.map((d, i) => ({
                            label: d.label,
                            data: d.data,
                            backgroundColor: outcomeColors[(d.label || '').toLowerCase()] || '#94a3b8',
                        })) },
                        options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } } }
                    });
                }
                if (platformChart.labels && platformChart.labels.length) {
                    new Chart(document.getElementById('chartPlatform'), {
                        type: 'bar',
                        data: { labels: platformChart.labels, datasets: platformChart.datasets.map((d, i) => ({
                            label: d.label,
                            data: d.data,
                            backgroundColor: outcomeColors[(d.label || '').toLowerCase()] || '#94a3b8',
                        })) },
                        options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } } }
                    });
                }
                if (funnelLabels.length) {
                    new Chart(document.getElementById('chartFunnel'), {
                        type: 'bar',
                        data: { labels: funnelLabels, datasets: [{ label: @json(__('Count')), data: funnelValues, backgroundColor: 'rgba(13, 110, 253, 0.35)' }] },
                        options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { grid: { display: false } } } }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
