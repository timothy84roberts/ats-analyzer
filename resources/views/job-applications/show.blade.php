<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row">
            <h1>{{ $application->title }}</h1>
            <a href="{{ route('applications.edit', $application) }}" class="admin-btn admin-btn--ghost">{{ __('Edit') }}</a>
        </div>
    </x-slot>

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
                    <div style="display:flex; flex-direction:column; gap: 6px; width: 100%;">
                        <span class="admin-activity__meta">{{ $note->created_at?->format('Y-m-d H:i') }}</span>
                        <div style="white-space: pre-line; color: var(--admin-text); margin: 0;">{{ $note->body }}</div>
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
