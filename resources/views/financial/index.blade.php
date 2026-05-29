<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--admin-accent-muted);color:var(--admin-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </div>
                <div>
                    <h1 style="margin:0;">{{ __('Financial') }}</h1>
                    <div style="font-size:0.8rem;color:var(--admin-text-muted);margin-top:1px;">
                        {{ $monthDate->format('F Y') }}
                        <span style="opacity:0.85;">· {{ $periodStart->format('d M') }} – {{ $periodEnd->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </x-slot>

    @if (session('status'))
        <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
    @endif

    {{-- ───────────────────── Stat Cards — all in one row ───────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-top:24px;">
        {{-- Income --}}
        <div class="admin-stat">
            <div class="admin-stat__label">{{ __('Income this month') }}</div>
            <div class="admin-stat__value-row">
                <span class="admin-stat__value" style="color:#16a34a;">{{ number_format($monthIncome, 0) }}</span>
            </div>
            <div class="admin-stat__icon" style="background:rgba(34,197,94,0.12);color:#16a34a;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/></svg>
            </div>
        </div>

        {{-- Expense --}}
        <div class="admin-stat">
            <div class="admin-stat__label">{{ __('Expense this month') }}</div>
            <div class="admin-stat__value-row">
                <span class="admin-stat__value" style="color:#dc2626;">{{ number_format($monthExpense, 0) }}</span>
            </div>
            <div class="admin-stat__icon admin-stat__icon--danger">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/></svg>
            </div>
        </div>
        {{-- Default Remaining Target --}}
        <div class="admin-stat fin-goal-card {{ $defaultMet ? 'fin-goal-card--met' : 'fin-goal-card--unmet' }}">
            <div class="admin-stat__label">{{ __('Default target') }}</div>
            <div class="admin-stat__value-row">
                <span class="admin-stat__value" style="color:{{ $defaultMet ? '#15803d' : '#dc2626' }};">
                    {{ number_format($settings->default_remaining, 0) }}
                </span>
                <span class="admin-stat__pct" style="color:{{ $defaultMet ? '#15803d' : '#dc2626' }};">
                    {{ $defaultMet ? '✓' : '✗' }}
                </span>
            </div>
            <div class="admin-stat__icon" style="{{ $defaultMet ? 'background:rgba(34,197,94,0.12);color:#15803d;' : 'background:rgba(239,68,68,0.12);color:#dc2626;' }}">
                @if ($defaultMet)
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                @else
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                @endif
            </div>
        </div>

        {{-- Additional Remaining Target --}}
        <div class="admin-stat fin-goal-card {{ $additionalMet ? 'fin-goal-card--met' : 'fin-goal-card--unmet' }}">
            <div class="admin-stat__label">{{ __('Additional target') }}</div>
            <div class="admin-stat__value-row">
                <span class="admin-stat__value" style="color:{{ $additionalMet ? '#15803d' : '#dc2626' }};">
                    {{ number_format($settings->additional_remaining, 0) }}
                </span>
                <span class="admin-stat__pct" style="color:{{ $additionalMet ? '#15803d' : '#dc2626' }};">
                    {{ $additionalMet ? '✓' : '✗' }}
                </span>
            </div>
            <div class="admin-stat__icon" style="{{ $additionalMet ? 'background:rgba(34,197,94,0.12);color:#15803d;' : 'background:rgba(239,68,68,0.12);color:#dc2626;' }}">
                @if ($additionalMet)
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                @else
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                @endif
            </div>
        </div>
    </div>

    {{-- ───────────────────────────── Year Chart ───────────────────────────── --}}
    <div class="admin-card">
        <div class="admin-chart-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <span>{{ __('Income & Expense — last 12 months') }}</span>
            <div style="display:flex;align-items:center;gap:16px;font-size:0.8rem;color:var(--admin-text-muted);">
                <span style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:3px;background:rgba(34,197,94,0.6);display:inline-block;"></span>{{ __('Income') }}</span>
                <span style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:3px;background:rgba(239,68,68,0.55);display:inline-block;"></span>{{ __('Expense') }}</span>
            </div>
        </div>
        <div class="admin-chart-body">
            <canvas id="chartFinancial" height="90"></canvas>
        </div>
    </div>

    {{-- ──────────────────────────── Transactions Card ──────────────────────── --}}
    <div class="admin-card" x-data="txManager()">

        {{-- Card header: title + month select + add button --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px 0;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:1rem;font-weight:600;color:var(--admin-text);">{{ __('Transactions') }}</div>
                <div style="font-size:0.8rem;color:var(--admin-text-muted);margin-top:2px;">{{ $monthTransactions->count() }} {{ __('entries') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;">
                {{-- Month selector --}}
                <select
                    class="admin-select"
                    style="height:38px;min-width:150px;"
                    @change="window.location.href='{{ route('financial.index') }}?month=' + $event.target.value"
                >
                    @foreach ($monthOptions as $val => $label)
                        <option value="{{ $val }}" @selected($val === $selectedMonth)>{{ $label }}</option>
                    @endforeach
                </select>
                <a href="{{ route('financial.pdf', ['month' => $selectedMonth]) }}"
                   class="admin-btn admin-btn--ghost"
                   style="height:38px;padding:0 14px;gap:6px;font-size:0.8rem;font-weight:600;text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;"
                   title="{{ __('Download PDF for') }} {{ $monthDate->format('F Y') }}">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    {{ __('Download PDF') }}
                </a>
                <button type="button" class="admin-btn admin-btn--primary" @click="openAdd()" style="gap:6px;">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Add') }}
                </button>
            </div>
        </div>

        {{-- Transactions table --}}
        @if ($monthTransactions->isEmpty())
            <div style="padding:48px 22px;text-align:center;color:var(--admin-text-muted);">
                <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                <p style="margin:0;font-size:0.9rem;">{{ __('No transactions for this month.') }}</p>
                <p style="margin:4px 0 0;font-size:0.8rem;">{{ __('Add your first income or expense entry.') }}</p>
            </div>
        @else
            <div class="admin-table-scroll" style="margin-top:16px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Note') }}</th>
                            <th style="width:96px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monthTransactions as $tx)
                            @php
                                $periods = app(\App\Services\FinancialPeriodService::class);
                                $txOverride = $periods->hasManualOverride($tx->transacted_at, $tx->reporting_month);
                            @endphp
                            <tr>
                                <td style="font-weight:500;">{{ $tx->title }}</td>
                                <td>
                                    @if ($tx->type === 'income')
                                        <span class="admin-pill" style="background:rgba(34,197,94,0.12);color:#16a34a;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/></svg>
                                            {{ __('Income') }}
                                        </span>
                                    @else
                                        <span class="admin-pill" style="background:rgba(239,68,68,0.12);color:#dc2626;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/></svg>
                                            {{ __('Expense') }}
                                        </span>
                                    @endif
                                </td>
                                <td style="font-variant-numeric:tabular-nums;font-weight:600;color:{{ $tx->type === 'income' ? '#16a34a' : '#dc2626' }};">
                                    {{ $tx->type === 'income' ? '+' : '−' }}{{ number_format($tx->amount, 2) }}
                                </td>
                                <td style="color:var(--admin-text-muted);font-size:0.875rem;">
                                    {{ $tx->transacted_at->format('d M Y') }}
                                    @if ($txOverride)
                                        <span class="admin-pill" style="display:inline-block;margin-left:6px;font-size:0.7rem;background:rgba(59,130,246,0.1);color:#2563eb;">
                                            {{ __('In :month', ['month' => $periods->reportingMonthLabel($tx->reporting_month)]) }}
                                        </span>
                                    @endif
                                </td>
                                <td style="color:var(--admin-text-muted);font-size:0.8125rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $tx->note ?: '—' }}
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:nowrap;">
                                        {{-- Edit --}}
                                        <button
                                            type="button"
                                            class="admin-btn admin-btn--ghost"
                                            style="height:32px;padding:0 10px;font-size:0.78rem;gap:4px;"
                                            @click="openEdit({
                                                id: {{ $tx->id }},
                                                title: {{ Js::from($tx->title) }},
                                                amount: '{{ $tx->amount }}',
                                                type: '{{ $tx->type }}',
                                                note: {{ Js::from($tx->note ?? '') }},
                                                transacted_at: '{{ $tx->transacted_at->format('Y-m-d') }}',
                                                assign_to_previous_month: {{ $txOverride ? 'true' : 'false' }}
                                            })"
                                        >
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                            {{ __('Edit') }}
                                        </button>
                                        {{-- Delete — opens confirm modal --}}
                                        <button
                                            type="button"
                                            class="admin-btn admin-btn--ghost"
                                            style="height:32px;padding:0 10px;font-size:0.78rem;gap:4px;color:#dc2626;border-color:rgba(239,68,68,0.25);"
                                            @click="openDelete({{ $tx->id }}, {{ Js::from($tx->title) }}, '{{ route('financial.transactions.destroy', $tx) }}')"
                                        >
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── Add Transaction Modal ── --}}
        <div
            x-show="addShow"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="position:fixed;inset:0;z-index:300;"
        >
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);" @click="closeAdd()"></div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:16px;pointer-events:none;">
                <div class="admin-modal-panel" style="position:relative;pointer-events:auto;width:100%;max-width:480px;border-radius:var(--admin-radius);padding:28px 28px 32px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;">
                        <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--admin-text);">{{ __('Add Transaction') }}</h2>
                        <button type="button" @click="closeAdd()" style="background:none;border:none;cursor:pointer;color:var(--admin-text-muted);padding:4px;line-height:1;">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="post" action="{{ route('financial.transactions.store') }}" class="admin-form-stack">
                        @csrf
                        <div class="admin-field">
                            <label class="admin-label" for="add_title">{{ __('Title') }}</label>
                            <input id="add_title" type="text" name="title" class="admin-input" placeholder="{{ __('e.g. Salary, Rent…') }}" required>
                        </div>
                        <div class="admin-field-grid-2">
                            <div class="admin-field">
                                <label class="admin-label" for="add_type">{{ __('Type') }}</label>
                                <select id="add_type" name="type" class="admin-select" x-model="addType">
                                    <option value="income">{{ __('Income') }}</option>
                                    <option value="expense">{{ __('Expense') }}</option>
                                </select>
                            </div>
                            <div class="admin-field">
                                <label class="admin-label" for="add_amount">{{ __('Amount') }}</label>
                                <input id="add_amount" type="number" name="amount" class="admin-input" min="0.01" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="add_date">{{ __('Date') }}</label>
                            <input id="add_date" type="date" name="transacted_at" class="admin-input" value="{{ now()->format('Y-m-d') }}" required @change="syncAddOverride()">
                        </div>
                        <div class="admin-field" x-show="addOverrideVisible" x-cloak style="padding:12px 14px;border-radius:8px;background:var(--admin-surface-muted, #f8fafc);border:1px solid var(--admin-border, #e5e7eb);">
                            <p style="margin:0 0 10px;font-size:0.8rem;color:var(--admin-text-muted);" x-text="addOverrideHint"></p>
                            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:0.875rem;color:var(--admin-text);">
                                <input type="checkbox" name="assign_to_previous_month" value="1" x-model="addAssignPrevious" style="margin-top:3px;">
                                <span x-text="addOverrideLabel"></span>
                            </label>
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="add_note">{{ __('Note') }} <span style="font-weight:400;text-transform:none;">({{ __('optional') }})</span></label>
                            <textarea id="add_note" name="note" class="admin-textarea" rows="2" style="min-height:70px;" placeholder="{{ __('Any extra details…') }}"></textarea>
                        </div>
                        <div class="admin-form-actions" style="margin-top:4px;">
                            <button type="submit" class="admin-btn admin-btn--primary" :style="addType==='income'?'background:#16a34a;':'background:#dc2626;'">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                <span x-text="addType==='income' ? '{{ __('Add Income') }}' : '{{ __('Add Expense') }}'"></span>
                            </button>
                            <button type="button" class="admin-btn admin-btn--ghost" @click="closeAdd()">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Edit Transaction Modal ── --}}
        <div
            x-show="editShow"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="position:fixed;inset:0;z-index:300;"
        >
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);" @click="closeEdit()"></div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:16px;pointer-events:none;">
                <div class="admin-modal-panel" style="position:relative;pointer-events:auto;width:100%;max-width:480px;border-radius:var(--admin-radius);padding:28px 28px 32px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;">
                        <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--admin-text);">{{ __('Edit Transaction') }}</h2>
                        <button type="button" @click="closeEdit()" style="background:none;border:none;cursor:pointer;color:var(--admin-text-muted);padding:4px;line-height:1;">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="post" :action="'{{ url('/financial/transactions') }}/' + editTx.id" class="admin-form-stack">
                        @csrf
                        @method('PUT')
                        <div class="admin-field">
                            <label class="admin-label" for="edit_title">{{ __('Title') }}</label>
                            <input id="edit_title" type="text" name="title" class="admin-input" x-model="editTx.title" required>
                        </div>
                        <div class="admin-field-grid-2">
                            <div class="admin-field">
                                <label class="admin-label" for="edit_type">{{ __('Type') }}</label>
                                <select id="edit_type" name="type" class="admin-select" x-model="editTx.type">
                                    <option value="income">{{ __('Income') }}</option>
                                    <option value="expense">{{ __('Expense') }}</option>
                                </select>
                            </div>
                            <div class="admin-field">
                                <label class="admin-label" for="edit_amount">{{ __('Amount') }}</label>
                                <input id="edit_amount" type="number" name="amount" class="admin-input" min="0.01" step="0.01" x-model="editTx.amount" required>
                            </div>
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="edit_date">{{ __('Date') }}</label>
                            <input id="edit_date" type="date" name="transacted_at" class="admin-input" x-model="editTx.transacted_at" required @change="syncEditOverride()">
                        </div>
                        <div class="admin-field" x-show="editOverrideVisible" x-cloak style="padding:12px 14px;border-radius:8px;background:var(--admin-surface-muted, #f8fafc);border:1px solid var(--admin-border, #e5e7eb);">
                            <p style="margin:0 0 10px;font-size:0.8rem;color:var(--admin-text-muted);" x-text="editOverrideHint"></p>
                            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:0.875rem;color:var(--admin-text);">
                                <input type="checkbox" name="assign_to_previous_month" value="1" x-model="editAssignPrevious" style="margin-top:3px;">
                                <span x-text="editOverrideLabel"></span>
                            </label>
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="edit_note">{{ __('Note') }} <span style="font-weight:400;text-transform:none;">({{ __('optional') }})</span></label>
                            <textarea id="edit_note" name="note" class="admin-textarea" rows="2" style="min-height:70px;" x-model="editTx.note"></textarea>
                        </div>
                        <div class="admin-form-actions" style="margin-top:4px;">
                            <button type="submit" class="admin-btn admin-btn--primary" :style="editTx.type==='income'?'background:#16a34a;':'background:#dc2626;'">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 3v4H7V3"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6"/></svg>
                                {{ __('Save Changes') }}
                            </button>
                            <button type="button" class="admin-btn admin-btn--ghost" @click="closeEdit()">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Delete Confirm Modal ── --}}
        <div
            x-show="deleteShow"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="position:fixed;inset:0;z-index:300;"
        >
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);" @click="closeDelete()"></div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:16px;pointer-events:none;">
                <div class="admin-modal-panel" style="position:relative;pointer-events:auto;width:100%;max-width:400px;border-radius:var(--admin-radius);padding:28px 28px 24px;">
                    {{-- Icon --}}
                    <div style="display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;background:rgba(239,68,68,0.1);color:#dc2626;margin:0 auto 16px;">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </div>
                    <h2 style="margin:0 0 8px;font-size:1.05rem;font-weight:700;color:var(--admin-text);text-align:center;">{{ __('Delete Transaction') }}</h2>
                    <p style="margin:0 0 22px;font-size:0.875rem;color:var(--admin-text-muted);text-align:center;line-height:1.5;">
                        {{ __('Are you sure you want to delete') }} <strong x-text="deleteTx.title" style="color:var(--admin-text);"></strong>{{ __('? This cannot be undone.') }}
                    </p>
                    <form method="post" :action="deleteTx.action">
                        @csrf
                        @method('DELETE')
                        <div style="display:flex;gap:10px;justify-content:center;">
                            <button type="button" class="admin-btn admin-btn--ghost" @click="closeDelete()" style="flex:1;">{{ __('Cancel') }}</button>
                            <button type="submit" class="admin-btn admin-btn--primary" style="flex:1;background:#dc2626;">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ───────────────────────────── Settings Card ───────────────────────────── --}}
    <div
        class="admin-card admin-card--pad"
        x-data="{ open: true }"
    >
        <div style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;" @click="open = !open">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--admin-surface-2);color:var(--admin-text-muted);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div style="font-size:0.95rem;font-weight:600;color:var(--admin-text);">{{ __('Savings Targets') }} <span style="font-weight:400;color:var(--admin-text-muted);font-size:0.85rem;">— {{ $monthDate->format('F Y') }}</span></div>
                    <div style="font-size:0.78rem;color:var(--admin-text-muted);">
                        {{ __('Default') }}: {{ number_format($settings->default_remaining, 0) }} &nbsp;·&nbsp;
                        {{ __('Additional') }}: {{ number_format($settings->additional_remaining, 0) }}
                    </div>
                </div>
            </div>
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--admin-text-muted);transition:transform 0.2s;" :style="open ? 'transform:rotate(180deg)' : ''"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        </div>

        <div x-show="open" x-cloak x-transition style="margin-top:22px;padding-top:20px;border-top:1px solid var(--admin-border);">
            <form method="post" action="{{ route('financial.settings.update') }}" class="admin-form-stack">
                @csrf
                <input type="hidden" name="year_month" value="{{ $selectedMonth }}">
                <div class="admin-field-grid-2">
                    <div class="admin-field">
                        <label class="admin-label" for="default_remaining">
                            <span style="display:flex;align-items:center;gap:6px;">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ __('Default Remaining') }}
                            </span>
                        </label>
                        <input id="default_remaining" type="number" name="default_remaining" class="admin-input" min="0" step="0.01" value="{{ old('default_remaining', $settings->default_remaining) }}" required>
                        <span style="font-size:0.75rem;color:var(--admin-text-muted);">{{ __('Success when net ≥ this value') }}</span>
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="additional_remaining">
                            <span style="display:flex;align-items:center;gap:6px;">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                                {{ __('Additional Remaining') }}
                            </span>
                        </label>
                        <input id="additional_remaining" type="number" name="additional_remaining" class="admin-input" min="0" step="0.01" value="{{ old('additional_remaining', $settings->additional_remaining) }}" required>
                        <span style="font-size:0.75rem;color:var(--admin-text-muted);">{{ __('Success when net − default ≥ this value') }}</span>
                    </div>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 3v4H7V3"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6"/></svg>
                        {{ __('Save Targets') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const fiscalPeriodEndDay = {{ \App\Services\FinancialPeriodService::PERIOD_END_DAY }};

            function txManager() {
                return {
                    // Add modal
                    addShow: false,
                    addType: 'income',
                    addAssignPrevious: false,
                    addOverrideVisible: false,
                    addOverrideHint: '',
                    addOverrideLabel: '',
                    openAdd() {
                        this.addShow = true;
                        this.addAssignPrevious = false;
                        this.$nextTick(() => this.syncAddOverride());
                    },
                    closeAdd() { this.addShow = false; },
                    syncAddOverride() {
                        const state = fiscalOverrideState(document.getElementById('add_date')?.value);
                        this.addOverrideVisible = state.visible;
                        this.addOverrideHint = state.hint;
                        this.addOverrideLabel = state.label;
                        if (!state.visible) this.addAssignPrevious = false;
                    },
                    // Edit modal
                    editShow: false,
                    editTx: { id: null, title: '', amount: '', type: 'income', note: '', transacted_at: '', assign_to_previous_month: false },
                    editAssignPrevious: false,
                    editOverrideVisible: false,
                    editOverrideHint: '',
                    editOverrideLabel: '',
                    openEdit(tx) {
                        this.editTx = { ...tx };
                        this.editAssignPrevious = !!tx.assign_to_previous_month;
                        this.editShow = true;
                        this.$nextTick(() => this.syncEditOverride());
                    },
                    closeEdit() { this.editShow = false; },
                    syncEditOverride() {
                        const state = fiscalOverrideState(this.editTx.transacted_at);
                        this.editOverrideVisible = state.visible;
                        this.editOverrideHint = state.hint;
                        this.editOverrideLabel = state.label;
                        if (!state.visible) this.editAssignPrevious = false;
                    },
                    // Delete confirm modal
                    deleteShow: false,
                    deleteTx: { id: null, title: '', action: '' },
                    openDelete(id, title, action) { this.deleteTx = { id, title, action }; this.deleteShow = true; },
                    closeDelete() { this.deleteShow = false; },
                };
            }

            function fiscalOverrideState(dateStr) {
                if (!dateStr) {
                    return { visible: false, hint: '', label: '' };
                }
                const [y, m, d] = dateStr.split('-').map(Number);
                if (d <= fiscalPeriodEndDay) {
                    return { visible: false, hint: '', label: '' };
                }
                const autoDate = new Date(y, m, 1);
                const prevDate = new Date(y, m - 1, 1);
                const autoLabel = autoDate.toLocaleString(undefined, { month: 'long', year: 'numeric' });
                const prevLabel = prevDate.toLocaleString(undefined, { month: 'long', year: 'numeric' });
                return {
                    visible: true,
                    hint: `{{ __('Normally counted in') }} ${autoLabel}`,
                    label: `{{ __('Count in previous month') }} (${prevLabel})`,
                };
            }

            const finLabels  = @json($chartLabels);
            const finIncome  = @json($chartIncome);
            const finExpense = @json($chartExpense);

            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Chart === 'undefined' || !finLabels.length) return;

                new Chart(document.getElementById('chartFinancial'), {
                    type: 'bar',
                    data: {
                        labels: finLabels,
                        datasets: [
                            {
                                label: '{{ __('Income') }}',
                                data: finIncome,
                                backgroundColor: 'rgba(34, 197, 94, 0.55)',
                                borderColor: 'rgba(34, 197, 94, 0.85)',
                                borderWidth: 1,
                                borderRadius: 4,
                            },
                            {
                                label: '{{ __('Expense') }}',
                                data: finExpense,
                                backgroundColor: 'rgba(239, 68, 68, 0.50)',
                                borderColor: 'rgba(239, 68, 68, 0.80)',
                                borderWidth: 1,
                                borderRadius: 4,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        return ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback(v) { return v.toLocaleString(); }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
