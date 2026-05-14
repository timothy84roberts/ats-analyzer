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
            <div class="admin-settings-card__icon admin-settings-card__icon--accent" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4.97 0 9 4.03 9 9s-4.03 9-9 9-9-4.03-9-9 4.03-9 9-9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3c-2.2 2.2-3.4 5.5-3.4 9s1.2 6.8 3.4 9m0-18c2.2 2.2 3.4 5.5 3.4 9s-1.2 6.8-3.4 9" />
                </svg>
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
            <div class="admin-settings-card__icon admin-settings-card__icon--accent" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <rect x="3.5" y="4.5" width="17" height="14" rx="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 8.5h17M9 19.5h6" />
                </svg>
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
</x-app-layout>
