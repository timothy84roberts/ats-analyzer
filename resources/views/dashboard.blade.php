<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--admin-accent-muted);color:var(--admin-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
            </div>
            <h1 style="margin:0;">{{ __('Dashboard') }}</h1>
        </div>
    </x-slot>

    @include('job-applications._pickers')

    @php
        $userFilterOptions = collect([['id' => '', 'name' => __('All')]])
            ->merge($filterUsers->map(fn ($u) => [
                'id' => (string) $u->id,
                'name' => $u->name,
            ]))->values();
        $countryFilterOptions = collect([['id' => '', 'name' => __('All'), 'flag' => null]])
            ->merge($filterCountries->map(fn ($c) => [
                'id' => (string) $c->id,
                'name' => $c->name,
                'flag' => $c->code ? 'https://flagcdn.com/24x18/'.strtolower($c->code).'.png' : null,
            ]))->values();
        $platformFilterOptions = collect([['id' => '', 'name' => __('All'), 'logo' => null, 'initial' => null]])
            ->merge($filterPlatforms->map(fn ($p) => [
                'id' => (string) $p->id,
                'name' => $p->name,
                'logo' => $p->logo_url,
                'initial' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p->name, 0, 1)),
            ]))->values();
        $selectedUserFilter = (string) request('user_id', '');
        $selectedCountryFilter = (string) request('country_id', '');
        $selectedPlatformFilter = (string) request('platform_id', '');
        $periodOffset = (int) ($periodOffset ?? 0);
        $navQuery = array_filter([
            'period' => $period ?? 'week',
            'user_id' => request('user_id'),
            'country_id' => request('country_id'),
            'platform_id' => request('platform_id'),
            'outcome_status' => request('outcome_status'),
        ], fn ($value) => $value !== null && $value !== '');
        $prevPeriodUrl = route('dashboard', array_merge($navQuery, ['offset' => $periodOffset - 1]));
        $nextPeriodUrl = route('dashboard', array_merge($navQuery, ['offset' => $periodOffset + 1]));
    @endphp

    <div class="admin-card admin-card--pad" style="overflow: visible;">
        <form method="get" action="{{ route('dashboard') }}" class="admin-toolbar" x-data="{ period: @js($period ?? 'week') }">
            <div class="admin-field">
                <span class="admin-label">{{ __('Period') }}</span>
                <div style="display:flex; align-items:center; gap:6px;">
                    <a href="{{ $prevPeriodUrl }}" class="dash-period-nav" aria-label="{{ __('Previous') }}">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </a>
                    <select name="period" class="admin-select" x-model="period" style="flex:1; min-width:120px;">
                        @foreach (['week' => __('By week'), 'month' => __('By month'), 'year' => __('By year')] as $val => $label)
                            <option value="{{ $val }}" @selected(($period ?? 'week') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <a href="{{ $nextPeriodUrl }}" class="dash-period-nav" aria-label="{{ __('Next') }}">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>
            </div>
            <div class="admin-field" style="min-width: 200px;" x-data="optionPicker(@js($userFilterOptions), @js($selectedUserFilter))">
                <span class="admin-label">{{ __('User') }}</span>
                <input type="hidden" name="user_id" x-model="selectedId">
                <div style="position: relative;">
                    <button type="button" class="admin-select" @click="open = !open" style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;">
                        <span x-text="selected() ? selected().name : '{{ __('All') }}'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        <span style="color: var(--admin-text-muted);">▾</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:220px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);">
                        <template x-for="user in options" :key="user.id">
                            <button type="button" @click="pick(user.id)" :style="selectedId === user.id ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                                <span x-text="user.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="admin-field" style="min-width: 220px;" x-data="optionPicker(@js($countryFilterOptions), @js($selectedCountryFilter))">
                <span class="admin-label">{{ __('Country') }}</span>
                <input type="hidden" name="country_id" x-model="selectedId">
                <div style="position: relative;">
                    <button type="button" class="admin-select" @click="open = !open" style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;">
                        <span style="display:flex; align-items:center; gap:8px; min-width:0;">
                            <template x-if="selected() && selected().flag">
                                <img :src="selected().flag" :alt="selected().name + ' flag'" width="20" height="14" style="border-radius:2px; object-fit:cover; flex-shrink:0;">
                            </template>
                            <span x-text="selected() ? selected().name : '{{ __('All') }}'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        </span>
                        <span style="color: var(--admin-text-muted);">▾</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:220px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);">
                        <template x-for="country in options" :key="country.id">
                            <button type="button" @click="pick(country.id)" :style="selectedId === country.id ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                                <template x-if="country.flag">
                                    <img :src="country.flag" :alt="country.name + ' flag'" width="20" height="14" style="border-radius:2px; object-fit:cover; flex-shrink:0;">
                                </template>
                                <span x-text="country.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="admin-field" style="min-width: 220px;" x-data="optionPicker(@js($platformFilterOptions), @js($selectedPlatformFilter))">
                <span class="admin-label">{{ __('Platform') }}</span>
                <input type="hidden" name="platform_id" x-model="selectedId">
                <div style="position: relative;">
                    <button type="button" class="admin-select" @click="open = !open" style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;">
                        <span style="display:flex; align-items:center; gap:8px; min-width:0;">
                            <template x-if="selected() && selected().logo">
                                <img :src="selected().logo" :alt="selected().name + ' logo'" width="20" height="20" style="border-radius:4px; object-fit:contain; flex-shrink:0; background:#fff;">
                            </template>
                            <span x-text="selected() ? selected().name : '{{ __('All') }}'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        </span>
                        <span style="color: var(--admin-text-muted);">▾</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:220px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);">
                        <template x-for="platform in options" :key="platform.id">
                            <button type="button" @click="pick(platform.id)" :style="selectedId === platform.id ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                                <template x-if="platform.logo">
                                    <img :src="platform.logo" :alt="platform.name + ' logo'" width="20" height="20" style="border-radius:4px; object-fit:contain; flex-shrink:0; background:#fff;">
                                </template>
                                <template x-if="!platform.logo && platform.initial">
                                    <span x-text="platform.initial" style="display:inline-flex; width:20px; height:20px; border-radius:4px; flex-shrink:0; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:var(--admin-accent); background:var(--admin-accent-muted);"></span>
                                </template>
                                <span x-text="platform.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="admin-field" style="min-width: 180px;" x-data="{ open: false, val: @js((string) request('outcome_status', '')) }">
                <span class="admin-label">{{ __('Outcome') }}</span>
                <input type="hidden" name="outcome_status" :value="val">
                <div style="position: relative;">
                    <button type="button" class="admin-select" @click="open = !open" style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;">
                        <span style="display:flex; align-items:center; gap:8px; min-width:0;">
                            <span style="display:none;" :style="val === '' ? 'display:inline-flex;align-items:center;gap:8px;' : 'display:none'">
                                <span>{{ __('All') }}</span>
                            </span>
                            @foreach ($outcomeOptions as $o)
                                <span style="display:none;" :style="val === '{{ $o }}' ? 'display:inline-flex;align-items:center;gap:8px;' : 'display:none'">
                                    <x-outcome-icon :status="$o" :size="16" />
                                    <span>{{ ucfirst($o) }}</span>
                                </span>
                            @endforeach
                        </span>
                        <span style="color: var(--admin-text-muted);">▾</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:240px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);">
                        <button type="button" @click="val = ''; open = false" :style="val === '' ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                            <span style="width:16px;flex-shrink:0;"></span>
                            <span>{{ __('All') }}</span>
                        </button>
                        @foreach ($outcomeOptions as $o)
                            <button type="button" @click="val = '{{ $o }}'; open = false" :style="val === '{{ $o }}' ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                                <x-outcome-icon :status="$o" :size="16" />
                                <span>{{ ucfirst($o) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
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
                    <x-outcome-icon :status="$o" :size="20" />
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

    @push('styles')
        <style>
            .dash-period-nav {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--admin-border);
                background: var(--admin-surface);
                color: var(--admin-text);
                border-radius: var(--admin-radius-sm);
                text-decoration: none;
                flex-shrink: 0;
                transition: background .15s;
            }
            .dash-period-nav:hover {
                background: var(--admin-surface-2);
            }
        </style>
    @endpush

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
