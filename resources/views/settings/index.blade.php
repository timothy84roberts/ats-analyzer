<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Settings') }}</h1>
    </x-slot>

    <p class="admin-muted-hint" style="margin: 0 0 24px;">
        {{ __('Manage your account, countries, platforms, and other options used when logging job applications.') }}
    </p>

    <div class="admin-settings-grid">
        <a href="{{ route('profile.edit') }}" class="admin-settings-card">
            <div class="admin-settings-card__icon admin-settings-card__icon--accent" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            </div>
            <div class="admin-settings-card__body">
                <h2 class="admin-settings-card__title">{{ __('Profile & security') }}</h2>
                <p class="admin-settings-card__desc">{{ __('Name, email, password, and account deletion.') }}</p>
            </div>
            <span class="admin-settings-card__chevron" aria-hidden="true">→</span>
        </a>

        <a href="{{ route('countries.index') }}" class="admin-settings-card">
            <div class="admin-settings-card__icon admin-settings-card__icon--muted" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3"/></svg>
            </div>
            <div class="admin-settings-card__body">
                <h2 class="admin-settings-card__title">{{ __('Countries') }}</h2>
                <p class="admin-settings-card__desc">
                    {{ __('Add countries from the online catalog; list is shared for application forms and filters.') }}
                    <span class="admin-settings-card__meta">{{ __(':count total', ['count' => $reference['country_count']]) }}</span>
                </p>
            </div>
            <span class="admin-settings-card__chevron" aria-hidden="true">→</span>
        </a>

        <a href="{{ route('platforms.index') }}" class="admin-settings-card">
            <div class="admin-settings-card__icon admin-settings-card__icon--muted" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6h12v.878M6 6h12M6 6v12m0-12L4.5 4.5M18 6v12m0-12l1.5-1.5M6 18h12M6 18l-1.5 1.5M18 18l1.5-1.5"/></svg>
            </div>
            <div class="admin-settings-card__body">
                <h2 class="admin-settings-card__title">{{ __('Platforms') }}</h2>
                <p class="admin-settings-card__desc">
                    {{ __('Job boards and application channels (slug, active flag, sort order).') }}
                    <span class="admin-settings-card__meta">{{ __(':count total', ['count' => $reference['platform_count']]) }}</span>
                </p>
            </div>
            <span class="admin-settings-card__chevron" aria-hidden="true">→</span>
        </a>
    </div>

    @can('use-ats-lab')
        <div class="admin-settings-grid" style="margin-top: 20px;">
            <a href="{{ route('ats.index') }}" class="admin-settings-card">
                <div class="admin-settings-card__icon admin-settings-card__icon--warn" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v4.125c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 013 17.25v-4.125z"/></svg>
                </div>
                <div class="admin-settings-card__body">
                    <h2 class="admin-settings-card__title">{{ __('ATS lab') }}</h2>
                    <p class="admin-settings-card__desc">{{ __('Testing tools for ATS analysis (when enabled for your account).') }}</p>
                </div>
                <span class="admin-settings-card__chevron" aria-hidden="true">→</span>
            </a>
        </div>
    @endcan
</x-app-layout>
