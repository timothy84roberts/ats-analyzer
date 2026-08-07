<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--admin-accent-muted);color:var(--admin-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                </div>
                <h1 style="margin:0;">{{ __('Job applications') }}</h1>
            </div>
            <a href="{{ route('applications.create') }}" class="admin-btn admin-btn--primary">{{ __('New application') }}</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
    @endif

    @include('job-applications._pickers')

    @php
        $userFilterOptions = collect([['id' => '', 'name' => __('All')]])
            ->merge($managedUsers->map(fn ($u) => [
                'id' => (string) $u->id,
                'name' => $u->name,
            ]))->values();
        $countryFilterOptions = collect([['id' => '', 'name' => __('All'), 'flag' => null]])
            ->merge($countries->map(fn ($c) => [
                'id' => (string) $c->id,
                'name' => $c->name,
                'flag' => $c->code ? 'https://flagcdn.com/24x18/'.strtolower($c->code).'.png' : null,
            ]))->values();
        $platformFilterOptions = collect([['id' => '', 'name' => __('All'), 'logo' => null, 'initial' => null]])
            ->merge($platforms->map(fn ($p) => [
                'id' => (string) $p->id,
                'name' => $p->name,
                'logo' => $p->logo_url,
                'initial' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p->name, 0, 1)),
            ]))->values();
        $selectedUserFilter = (string) request('user_id', '');
        $selectedCountryFilter = (string) request('country_id', '');
        $selectedPlatformFilter = (string) request('platform_id', '');
    @endphp

    <div class="admin-card admin-card--pad" style="overflow: visible;">
        <form method="get" class="admin-toolbar">
            <div class="admin-field admin-field--grow">
                <span class="admin-label">{{ __('Search title') }}</span>
                <input type="text" name="q" value="{{ request('q') }}" class="admin-input" placeholder="{{ __('Job title…') }}" onkeydown="if(event.key==='Enter'){event.preventDefault();this.form.requestSubmit();}">
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
                            <button type="button" @click="pick(user.id); $nextTick(() => $el.closest('form').requestSubmit())" :style="selectedId === user.id ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
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
                            <button type="button" @click="pick(country.id); $nextTick(() => $el.closest('form').requestSubmit())" :style="selectedId === country.id ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
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
                            <button type="button" @click="pick(platform.id); $nextTick(() => $el.closest('form').requestSubmit())" :style="selectedId === platform.id ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
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
                            @foreach ($outcomeStatuses as $o)
                                <span style="display:none;" :style="val === '{{ $o }}' ? 'display:inline-flex;align-items:center;gap:8px;' : 'display:none'">
                                    <x-outcome-icon :status="$o" :size="16" />
                                    <span>{{ ucfirst($o) }}</span>
                                </span>
                            @endforeach
                        </span>
                        <span style="color: var(--admin-text-muted);">▾</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:240px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);">
                        <button type="button" @click="val = ''; open = false; $nextTick(() => $el.closest('form').requestSubmit())" :style="val === '' ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                            <span style="width:16px;flex-shrink:0;"></span>
                            <span>{{ __('All') }}</span>
                        </button>
                        @foreach ($outcomeStatuses as $o)
                            <button type="button" @click="val = '{{ $o }}'; open = false; $nextTick(() => $el.closest('form').requestSubmit())" :style="val === '{{ $o }}' ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                                <x-outcome-icon :status="$o" :size="16" />
                                <span>{{ ucfirst($o) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="admin-field">
                <span class="admin-label">{{ __('Applied on') }}</span>
                <input type="date" name="applied_on" value="{{ request('applied_on') }}" class="admin-input" onchange="this.form.requestSubmit()">
            </div>
        </form>
    </div>

    <div class="admin-table-wrap" style="margin-top: 20px;">
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Company') }}</th>
                        <th style="width: 160px;">{{ __('Country') }}</th>
                        <th>{{ __('Platform') }}</th>
                        <th style="width: 190px;">{{ __('Stage') }}</th>
                        <th>{{ __('Outcome') }}</th>
                        <th style="width: 120px;">{{ __('Applied') }}</th>
                        <th style="width: 250px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $app)
                        <tr>
                            <td><a href="{{ route('applications.show', $app) }}">{{ $app->title }}</a></td>
                            <td>
                                @if ($app->user)
                                    @php($hue = ($app->user->id * 47) % 360)
                                    <span class="admin-pill" style="display:inline-flex;align-items:center;background:hsla({{ $hue }}, 55%, 42%, 0.14);color:hsl({{ $hue }}, 55%, 32%);">
                                        {{ $app->user->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $app->company_name ?? '—' }}</td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 6px;">
                                    @if ($app->country?->code)
                                        <img
                                            src="{{ 'https://flagcdn.com/24x18/'.strtolower($app->country->code).'.png' }}"
                                            alt="{{ $app->country->name }} flag"
                                            width="20"
                                            height="14"
                                            style="border-radius:2px; object-fit:cover; flex-shrink:0;"
                                        >
                                    @endif
                                    <span>{{ $app->country?->name }}</span>
                                </span>
                            </td>
                            <td>
                                <span style="display:inline-flex; align-items:center; gap:8px;">
                                    @if ($app->platform?->logo_url)
                                        <img
                                            src="{{ $app->platform->logo_url }}"
                                            alt="{{ $app->platform->name }} logo"
                                            width="18"
                                            height="18"
                                            loading="lazy"
                                            style="border-radius:4px; object-fit:contain; flex-shrink:0; background:#fff;"
                                        >
                                    @endif
                                    <span>{{ $app->platform?->name }}</span>
                                </span>
                            </td>
                            <td>
                                @php($stageSlug = $app->pipelineStage?->slug ?? 'unknown')
                                <span class="admin-pill admin-pill--stage admin-pill--stage-{{ $stageSlug }}" style="display:inline-flex;align-items:center;gap:6px;">
                                    <x-stage-icon :slug="$stageSlug" />
                                    {{ $app->pipelineStage?->label ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="admin-pill admin-pill--{{ $app->outcome_status }}" style="display:inline-flex;align-items:center;gap:6px;">
                                    <x-outcome-icon :status="$app->outcome_status" />
                                    {{ ucfirst($app->outcome_status) }}
                                </span>
                            </td>
                            <td>{{ $app->applied_on->format('Y-m-d') }}</td>
                            <td class="whitespace-nowrap">
                                <div class="admin-table-actions" x-data="{ noteOpen: false, callOpen: false }" style="flex-wrap: wrap; gap: 6px;">
                                    <a
                                        href="{{ route('applications.edit', ['application' => $app, 'return_to' => request()->fullUrl()]) }}"
                                        class="admin-btn admin-btn--ghost"
                                        aria-label="{{ __('Edit application') }}"
                                        title="{{ __('Edit') }}"
                                        style="height: 36px; width: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 20h4l10-10-4-4L4 16v4z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M13 7l4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <button type="button" class="admin-btn admin-btn--ghost" style="height: 36px; padding: 0 14px;" @click="noteOpen = true">{{ __('Add note') }}</button>
                                    <button
                                        type="button"
                                        class="admin-btn admin-btn--ghost"
                                        aria-label="{{ __('Book call') }}"
                                        title="{{ __('Book call') }}"
                                        style="height: 36px; width: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        @click="callOpen = true"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path
                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                    <form action="{{ route('applications.destroy', $app) }}" method="post" onsubmit="return confirm(@json(__('Delete this application?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="admin-btn admin-btn--danger"
                                            aria-label="{{ __('Delete application') }}"
                                            title="{{ __('Delete') }}"
                                            style="height: 36px; width: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M6 6l1 14h10l1-14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M10 10v7M14 10v7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <template x-if="noteOpen">
                                        <div
                                            class="admin-modal-backdrop"
                                            x-cloak
                                            @click="noteOpen = false"
                                            @keydown.escape.window="noteOpen = false"
                                            style="position: fixed; inset: 0; background: rgba(0,0,0,.55); display: flex; align-items: center; justify-content: center; padding: 24px; z-index: 100;"
                                        >
                                            <div
                                                class="admin-card admin-card--pad"
                                                @click.stop
                                                style="width: min(640px, 100%);"
                                            >
                                                <div style="display:flex; align-items:center; justify-content:space-between; gap: 12px;">
                                                    <h3 style="margin: 0;">{{ __('Add note') }}</h3>
                                                    <button type="button" class="admin-btn admin-btn--ghost" @click="noteOpen = false">{{ __('Close') }}</button>
                                                </div>
                                                <form method="post" action="{{ route('applications.notes.store', $app) }}" style="margin-top: 14px; width: 100%;">
                                                    @csrf
                                                    <div class="admin-field">
                                                        <label class="admin-label" for="note-body-{{ $app->id }}">{{ __('Note') }}</label>
                                                        <textarea id="note-body-{{ $app->id }}" name="body" rows="5" class="admin-textarea" placeholder="{{ __('Write a note…') }}" required autofocus></textarea>
                                                    </div>
                                                    <div style="display:flex; gap: 10px; justify-content:flex-end; margin-top: 14px;">
                                                        <button type="button" class="admin-btn admin-btn--ghost" @click="noteOpen = false">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save note') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="callOpen">
                                        <div
                                            class="admin-modal-backdrop"
                                            x-cloak
                                            @click="callOpen = false"
                                            @keydown.escape.window="callOpen = false"
                                            style="position: fixed; inset: 0; background: rgba(0,0,0,.55); display: flex; align-items: center; justify-content: center; padding: 24px; z-index: 100;"
                                        >
                                            <div
                                                class="admin-card admin-card--pad"
                                                @click.stop
                                                style="width: min(640px, 100%);"
                                            >
                                                <div style="display:flex; align-items:center; justify-content:space-between; gap: 12px;">
                                                    <h3 style="margin: 0;">{{ __('Book call') }}</h3>
                                                    <button type="button" class="admin-btn admin-btn--ghost" @click="callOpen = false">{{ __('Close') }}</button>
                                                </div>
                                                <form method="post" action="{{ route('applications.calls.store', $app) }}" class="admin-form-stack admin-book-call-form" style="margin-top: 18px; width: 100%;">
                                                    @csrf
                                                    <div class="admin-field">
                                                        <label class="admin-label" for="call-user-{{ $app->id }}">{{ __('User') }}</label>
                                                        <select id="call-user-{{ $app->id }}" name="user_id" class="admin-select" required>
                                                            <option value="">{{ __('Select user') }}</option>
                                                            @foreach ($managedUsers as $managedUser)
                                                                <option value="{{ $managedUser->id }}" @selected((string) $app->user_id === (string) $managedUser->id)>{{ $managedUser->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="admin-field">
                                                        <label class="admin-label" for="call-title-{{ $app->id }}">{{ __('Title') }}</label>
                                                        <input id="call-title-{{ $app->id }}" type="text" name="title" class="admin-input" placeholder="{{ __('e.g. Phone screen with recruiter') }}" required>
                                                    </div>
                                                    <div class="admin-field">
                                                        <label class="admin-label" for="call-desc-{{ $app->id }}">{{ __('Description') }}</label>
                                                        <textarea id="call-desc-{{ $app->id }}" name="description" rows="4" class="admin-textarea" placeholder="{{ __('Agenda, link, dial-in…') }}"></textarea>
                                                    </div>
                                                    <div class="admin-field">
                                                        <label class="admin-label" for="call-when-{{ $app->id }}">{{ __('Date & time') }}</label>
                                                        <input id="call-when-{{ $app->id }}" type="datetime-local" name="scheduled_at" class="admin-input" required>
                                                    </div>
                                                    <div style="display:flex; gap: 10px; justify-content:flex-end; padding-top: 4px;">
                                                        <button type="button" class="admin-btn admin-btn--ghost" @click="callOpen = false">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save call') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align: center; padding: 48px; color: var(--admin-text-muted);">{{ __('No applications yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            {{ $applications->links() }}
        </div>
    </div>
</x-app-layout>
