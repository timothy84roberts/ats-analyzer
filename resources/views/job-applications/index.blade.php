<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row">
            <h1>{{ __('Job applications') }}</h1>
            <a href="{{ route('applications.create') }}" class="admin-btn admin-btn--primary">{{ __('New application') }}</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
    @endif

    <div class="admin-card admin-card--pad">
        <form method="get" class="admin-toolbar">
            <div class="admin-field admin-field--grow">
                <span class="admin-label">{{ __('Search title') }}</span>
                <input type="text" name="q" value="{{ request('q') }}" class="admin-input" placeholder="{{ __('Job title…') }}">
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Country') }}</span>
                <select name="country_id" class="admin-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}" @selected((string) request('country_id') === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Platform') }}</span>
                <select name="platform_id" class="admin-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($platforms as $p)
                        <option value="{{ $p->id }}" @selected((string) request('platform_id') === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Outcome') }}</span>
                <select name="outcome_status" class="admin-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($outcomeStatuses as $o)
                        <option value="{{ $o }}" @selected(request('outcome_status') === $o)>{{ ucfirst($o) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field" style="align-self: flex-end;">
                <span class="admin-label" style="opacity:0;">&nbsp;</span>
                <button type="submit" class="admin-btn admin-btn--ghost">{{ __('Filter') }}</button>
            </div>
        </form>
    </div>

    <div class="admin-table-wrap" style="margin-top: 20px;">
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Country') }}</th>
                        <th>{{ __('Platform') }}</th>
                        <th>{{ __('Stage') }}</th>
                        <th>{{ __('Outcome') }}</th>
                        <th>{{ __('Applied') }}</th>
                        <th style="width: 140px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $app)
                        <tr>
                            <td><a href="{{ route('applications.show', $app) }}">{{ $app->title }}</a></td>
                            <td>{{ $app->company_name ?? '—' }}</td>
                            <td>{{ $app->country?->name }}</td>
                            <td>{{ $app->platform?->name }}</td>
                            <td>{{ $app->pipelineStage?->label }}</td>
                            <td>
                                <span class="admin-pill admin-pill--{{ $app->outcome_status }}">{{ ucfirst($app->outcome_status) }}</span>
                            </td>
                            <td>{{ $app->applied_on->format('Y-m-d') }}</td>
                            <td class="whitespace-nowrap">
                                <div class="admin-table-actions">
                                    <a href="{{ route('applications.edit', $app) }}" class="admin-btn admin-btn--ghost" style="height: 36px; padding: 0 14px;">{{ __('Edit') }}</a>
                                    <form action="{{ route('applications.destroy', $app) }}" method="post" onsubmit="return confirm(@json(__('Delete this application?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--danger">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align: center; padding: 48px; color: var(--admin-text-muted);">{{ __('No applications yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            {{ $applications->links() }}
        </div>
    </div>
</x-app-layout>
