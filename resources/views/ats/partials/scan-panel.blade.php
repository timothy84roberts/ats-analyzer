@php
    /** @var array<string, mixed>|null $normalized */
    $norm = isset($normalized) && is_array($normalized) ? $normalized : null;
    /** @var string|null $atsError */
    $atsError = isset($atsError) && is_string($atsError) && $atsError !== '' ? $atsError : null;
@endphp
<aside class="ats-scan-panel" aria-label="{{ __('Match results') }}">
    @if ($norm !== null)
        @php
            $overall = $norm['overall_percent'] ?? $norm['score'] ?? null;
            $skills = $norm['skills_percent'] ?? null;
            $keywords = $norm['keywords_percent'] ?? null;
            $skillChips = $norm['skill_chips'] ?? [];
            $keywordChips = $norm['keyword_chips'] ?? [];
            $tips = $norm['tips'] ?? [];
            $statusMsg = $norm['status_message'] ?? null;
        @endphp
        <div class="ats-scan-panel__inner">
            <div class="ats-scan-score-row">
                <div>
                    <div class="ats-scan-score">{{ $overall !== null ? (int) round((float) $overall) : '—' }}@if ($overall !== null)<span class="ats-scan-score__pct">%</span>@endif</div>
                    <div class="ats-scan-score__label">{{ __('MATCH SCORE') }}</div>
                    @if ($statusMsg)
                        <div class="ats-scan-status-msg">{{ $statusMsg }}</div>
                    @endif
                </div>
                <div class="ats-scan-bars">
                    <div class="ats-scan-bar">
                        <span class="ats-scan-bar__label">{{ __('Overall') }}</span>
                        <div class="ats-scan-bar__track"><span class="ats-scan-bar__fill ats-scan-bar__fill--overall" style="width: {{ $overall !== null ? min(100, max(0, (float) $overall)) : 0 }}%;"></span></div>
                        <span class="ats-scan-bar__val">{{ $overall !== null ? (int) round((float) $overall).'%' : '—' }}</span>
                    </div>
                    <div class="ats-scan-bar">
                        <span class="ats-scan-bar__label">{{ __('Skills') }}</span>
                        <div class="ats-scan-bar__track"><span class="ats-scan-bar__fill ats-scan-bar__fill--skills" style="width: {{ $skills !== null ? min(100, max(0, (float) $skills)) : 0 }}%;"></span></div>
                        <span class="ats-scan-bar__val">{{ $skills !== null ? (int) round((float) $skills) : '—' }}</span>
                    </div>
                    <div class="ats-scan-bar">
                        <span class="ats-scan-bar__label">{{ __('Keywords') }}</span>
                        <div class="ats-scan-bar__track"><span class="ats-scan-bar__fill ats-scan-bar__fill--keywords" style="width: {{ $keywords !== null ? min(100, max(0, (float) $keywords)) : 0 }}%;"></span></div>
                        <span class="ats-scan-bar__val">{{ $keywords !== null ? (int) round((float) $keywords) : '—' }}</span>
                    </div>
                </div>
            </div>

            <a href="#ats-scan-form" class="ats-scan-cta">{{ __('Enhance Again') }}</a>

            <section class="ats-scan-section">
                <div class="ats-scan-section__head">
                    <span class="ats-scan-dot ats-scan-dot--skills"></span>
                    <h2 class="ats-scan-section__title">{{ __('Skills') }} ({{ count($skillChips) }})</h2>
                    <span class="ats-scan-section__hint">{{ __('Metrics from match model') }}</span>
                </div>
                <div class="ats-scan-chips">
                    @forelse ($skillChips as $chip)
                        <span class="ats-scan-chip ats-scan-chip--skills">{{ $chip['label'] ?? '' }} @if (isset($chip['value']))<span class="ats-scan-chip__n">{{ (int) $chip['value'] }}</span>@endif</span>
                    @empty
                        <span class="ats-scan-chip ats-scan-chip--muted">{{ __('No skill breakdown returned') }}</span>
                    @endforelse
                </div>
            </section>

            <section class="ats-scan-section">
                <div class="ats-scan-section__head">
                    <span class="ats-scan-dot ats-scan-dot--keywords"></span>
                    <h2 class="ats-scan-section__title">{{ __('Keywords / stack') }} ({{ count($keywordChips) }})</h2>
                    <span class="ats-scan-section__hint">{{ __('Secondary match signals') }}</span>
                </div>
                <div class="ats-scan-chips">
                    @forelse ($keywordChips as $chip)
                        <span class="ats-scan-chip ats-scan-chip--keywords">{{ $chip['label'] ?? '' }} @if (isset($chip['value']))<span class="ats-scan-chip__n">{{ (int) $chip['value'] }}</span>@endif</span>
                    @empty
                        <span class="ats-scan-chip ats-scan-chip--muted">{{ __('No keyword breakdown returned') }}</span>
                    @endforelse
                </div>
            </section>

            @if (count($tips) > 0)
                <section class="ats-scan-section">
                    <div class="ats-scan-section__head">
                        <span class="ats-scan-dot ats-scan-dot--tips"></span>
                        <h2 class="ats-scan-section__title">{{ __('Tips & explanations') }}</h2>
                    </div>
                    <ul class="ats-scan-tips">
                        @foreach ($tips as $tip)
                            <li>{{ $tip }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>
    @elseif ($atsError !== null)
        <div class="ats-scan-panel__inner ats-scan-panel__empty">
            <p class="ats-scan-empty-title">{{ __('Analysis failed') }}</p>
            <p class="ats-scan-empty-desc">{{ $atsError }}</p>
            <a href="#ats-scan-form" class="ats-scan-cta">{{ __('Try again') }}</a>
        </div>
    @else
        <div class="ats-scan-panel__inner ats-scan-panel__empty">
            <p class="ats-scan-empty-title">{{ __('Results will appear here') }}</p>
            <p class="ats-scan-empty-desc">{{ __('Upload a resume, paste a job description, and run Analyze match.') }}</p>
        </div>
    @endif
</aside>
