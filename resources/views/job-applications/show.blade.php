<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row">
            <h1>{{ $application->title }}</h1>
            <a href="{{ route('applications.edit', $application) }}" class="admin-btn admin-btn--ghost">{{ __('Edit') }}</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
    @endif

    <div class="admin-card" style="overflow: hidden;">
        <dl class="admin-dl">
            <div class="admin-dl__row">
                <dt class="admin-dl__dt">{{ __('Outcome') }}</dt>
                <dd class="admin-dl__dd"><span class="admin-pill admin-pill--{{ $application->outcome_status }}">{{ ucfirst($application->outcome_status) }}</span></dd>
            </div>
            @if ($application->isRejected() && $application->rejection_reason)
                <div class="admin-dl__row">
                    <dt class="admin-dl__dt">{{ __('Rejection reason') }}</dt>
                    <dd class="admin-dl__dd" style="white-space: pre-wrap;">{{ $application->rejection_reason }}</dd>
                </div>
            @endif
            <div class="admin-dl__row">
                <dt class="admin-dl__dt">{{ __('Pipeline stage') }}</dt>
                <dd class="admin-dl__dd">
                    @php($currentStageSlug = $application->pipelineStage?->slug ?? 'unknown')
                    <span class="admin-pill admin-pill--stage admin-pill--stage-{{ $currentStageSlug }}">
                        {{ $application->pipelineStage?->label ?? '—' }}
                    </span>
                </dd>
            </div>
            <div class="admin-dl__row">
                <dt class="admin-dl__dt">{{ __('Country / Platform') }}</dt>
                <dd class="admin-dl__dd" style="display: inline-flex; align-items: center; gap: 6px;">
                    @if ($application->country?->code)
                        <img
                            src="{{ 'https://flagcdn.com/24x18/'.strtolower($application->country->code).'.png' }}"
                            alt="{{ $application->country->name }} flag"
                            width="20"
                            height="14"
                            style="border-radius:2px; object-fit:cover; flex-shrink:0;"
                        >
                    @endif
                    <span>{{ $application->country?->name }} / {{ $application->platform?->name }}</span>
                </dd>
            </div>
            <div class="admin-dl__row">
                <dt class="admin-dl__dt">{{ __('Company') }}</dt>
                <dd class="admin-dl__dd">{{ $application->company_name ?? '—' }}</dd>
            </div>
            <div class="admin-dl__row">
                <dt class="admin-dl__dt">{{ __('Applied on') }}</dt>
                <dd class="admin-dl__dd">{{ $application->applied_on->format('Y-m-d') }}</dd>
            </div>
            @if ($application->analysis_percentage !== null)
                <div class="admin-dl__row">
                    <dt class="admin-dl__dt">{{ __('Analysis %') }}</dt>
                    <dd class="admin-dl__dd">{{ $application->analysis_percentage }}</dd>
                </div>
            @endif
            @if ($application->description)
                <div class="admin-dl__row">
                    <dt class="admin-dl__dt">{{ __('Description / notes') }}</dt>
                    <dd class="admin-dl__dd admin-rich-text">{!! \App\Support\RichTextSanitizer::sanitize($application->description) !!}</dd>
                </div>
            @endif
            @if ($application->hasResume())
                <div class="admin-dl__row">
                    <dt class="admin-dl__dt">{{ __('Resume') }}</dt>
                    <dd class="admin-dl__dd">
                        <a href="{{ route('applications.resume', $application) }}" target="_blank" rel="noopener" class="admin-link">{{ __('Open PDF in new tab') }}</a>
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="admin-subcard" style="margin-top: 20px;" x-data="{ noteOpen: false }">
        <div style="display:flex; align-items:center; justify-content:space-between; gap: 12px;">
            <h3 class="admin-subcard__title" style="margin: 0;">{{ __('Notes') }}</h3>
            <button type="button" class="admin-btn admin-btn--ghost" @click="noteOpen = true">{{ __('Add note') }}</button>
        </div>
        <ul class="admin-activity" style="margin: 0;">
            @forelse ($application->notes as $note)
                <li class="admin-activity__item" style="align-items: flex-start;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; width: 100%;">
                        <div style="display:flex; flex-direction:column; gap: 6px; flex: 1; min-width: 0;">
                            <span class="admin-activity__meta">{{ $note->created_at?->format('Y-m-d H:i') }}</span>
                            <div style="white-space: pre-line; color: var(--admin-text); margin: 0;">{{ $note->body }}</div>
                        </div>
                        <form
                            action="{{ route('applications.notes.destroy', [$application, $note]) }}"
                            method="post"
                            onsubmit="return confirm(@json(__('Delete this note?')));"
                            style="flex-shrink: 0; margin: 0;"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="admin-btn admin-btn--danger"
                                aria-label="{{ __('Delete note') }}"
                                title="{{ __('Delete') }}"
                                style="height: 32px; width: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 6h18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6 6l1 14h10l1-14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 10v7M14 10v7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="admin-activity__item" style="border: none; padding-top: 0;">
                    <span style="color: var(--admin-text-muted);">{{ __('No notes yet.') }}</span>
                </li>
            @endforelse
        </ul>

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
                    <form method="post" action="{{ route('applications.notes.store', $application) }}" style="margin-top: 14px; width: 100%;">
                        @csrf
                        <div class="admin-field">
                            <label class="admin-label" for="note-body">{{ __('Note') }}</label>
                            <textarea id="note-body" name="body" rows="5" class="admin-textarea" placeholder="{{ __('Write a note…') }}" required autofocus></textarea>
                        </div>
                        <div style="display:flex; gap: 10px; justify-content:flex-end; margin-top: 14px;">
                            <button type="button" class="admin-btn admin-btn--ghost" @click="noteOpen = false">{{ __('Cancel') }}</button>
                            <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save note') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <div id="scheduled-calls" class="admin-subcard" style="margin-top: 20px; scroll-margin-top: 96px;" x-data="{ callOpen: false }">
        <div style="display:flex; align-items:center; justify-content:space-between; gap: 12px;">
            <h3 class="admin-subcard__title" style="margin: 0;">{{ __('Scheduled calls') }}</h3>
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
        </div>
        <ul class="admin-activity" style="margin: 0;">
            @forelse ($application->calls as $call)
                <li class="admin-activity__item" style="align-items: flex-start;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; width: 100%;">
                        <div style="display:flex; flex-direction:column; gap: 6px; flex: 1; min-width: 0;">
                            <div style="display:flex; flex-wrap: wrap; align-items: baseline; gap: 8px;">
                                <strong style="color: var(--admin-text);">{{ $call->title }}</strong>
                                <span class="admin-activity__meta">{{ $call->scheduled_at?->format('Y-m-d H:i') }}</span>
                            </div>
                            @if ($call->description)
                                <div style="white-space: pre-line; color: var(--admin-text); margin: 0;">{{ $call->description }}</div>
                            @endif
                        </div>
                        <form
                            action="{{ route('applications.calls.destroy', [$application, $call]) }}"
                            method="post"
                            onsubmit="return confirm(@json(__('Delete this call?')));"
                            style="flex-shrink: 0; margin: 0;"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="admin-btn admin-btn--danger"
                                aria-label="{{ __('Delete call') }}"
                                title="{{ __('Delete') }}"
                                style="height: 32px; width: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 6h18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6 6l1 14h10l1-14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 10v7M14 10v7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="admin-activity__item" style="border: none; padding-top: 0;">
                    <span style="color: var(--admin-text-muted);">{{ __('No calls scheduled yet.') }}</span>
                </li>
            @endforelse
        </ul>

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
                    <form method="post" action="{{ route('applications.calls.store', $application) }}" class="admin-form-stack admin-book-call-form" style="margin-top: 18px; width: 100%;">
                        @csrf
                        <div class="admin-field">
                            <label class="admin-label" for="call-title">{{ __('Title') }}</label>
                            <input id="call-title" type="text" name="title" class="admin-input" value="{{ old('title') }}" placeholder="{{ __('e.g. Phone screen with recruiter') }}" required autofocus>
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="call-description">{{ __('Description') }}</label>
                            <textarea id="call-description" name="description" rows="4" class="admin-textarea" placeholder="{{ __('Agenda, link, dial-in…') }}">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="call-scheduled-at">{{ __('Date & time') }}</label>
                            <input id="call-scheduled-at" type="datetime-local" name="scheduled_at" class="admin-input" value="{{ old('scheduled_at') }}" required>
                            <x-input-error :messages="$errors->get('scheduled_at')" class="mt-2" />
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

    @if ($application->hasResume())
        <div class="admin-card admin-card--pad" style="margin-top: 20px;">
            <h2 class="admin-subcard__title" style="margin-top: 0;">{{ __('Resume preview') }}</h2>
            <iframe class="admin-resume-iframe" title="{{ __('Resume PDF') }}" src="{{ route('applications.resume', $application) }}"></iframe>
        </div>
    @endif

    <div class="admin-subcard" style="margin-top: 20px;">
        <h3 class="admin-subcard__title">{{ __('Stage history') }}</h3>
        <ul class="admin-activity">
            @forelse ($application->stageHistories as $h)
                <li class="admin-activity__item">
                    @php($historyStageSlug = $h->pipelineStage?->slug ?? 'unknown')
                    <span class="admin-pill admin-pill--stage admin-pill--stage-{{ $historyStageSlug }}">
                        {{ $h->pipelineStage?->label ?? '—' }}
                    </span>
                    <span class="admin-activity__meta">{{ $h->entered_at->format('Y-m-d H:i') }}</span>
                </li>
            @empty
                <li class="admin-activity__item" style="border: none; padding-top: 0;">
                    <span style="color: var(--admin-text-muted);">{{ __('No history.') }}</span>
                </li>
            @endforelse
        </ul>
    </div>
</x-app-layout>
