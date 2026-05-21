<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--admin-accent-muted);color:var(--admin-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h1 style="margin:0;">{{ __('Settings') }}</h1>
        </div>
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
