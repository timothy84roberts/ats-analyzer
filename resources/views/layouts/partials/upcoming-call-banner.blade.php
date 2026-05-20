@if ($upcomingCallBannerCalls->isNotEmpty())
    <div
        class="admin-alert admin-alert--warn admin-upcoming-call-banner"
        style="margin-top: 0; margin-bottom: 16px;"
        role="region"
        aria-label="{{ __('Upcoming calls') }}"
    >
        <strong style="display: block; margin-bottom: 8px;">{{ __('Upcoming call reminder') }}</strong>
        <p style="margin: 0 0 10px; font-size: 0.8125rem; color: var(--admin-text-muted); line-height: 1.45;">
            {{ __('You have at least one call scheduled in the next 12 hours. Open the application to review details.') }}
        </p>
        <ul style="margin: 0; padding-left: 1.15rem; list-style: disc;">
            @foreach ($upcomingCallBannerCalls as $call)
                <li style="margin: 6px 0;">
                    <a
                        href="{{ route('applications.show', $call->application) }}#scheduled-calls"
                        class="admin-link"
                        style="font-weight: 600;"
                    >
                        {{ $call->title }}
                    </a>
                    <span style="color: var(--admin-text-muted);">
                        — {{ $call->application->title }}
                        · {{ $call->scheduled_at->format('Y-m-d H:i') }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
