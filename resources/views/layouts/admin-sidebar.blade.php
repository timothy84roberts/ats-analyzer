@php
    $isDash = request()->routeIs('dashboard');
    $isApps = request()->routeIs('applications.*');
    $isAts = request()->routeIs('ats.*');
    $isSettings = request()->routeIs('settings.*') || request()->routeIs('countries.*') || request()->routeIs('platforms.*') || request()->routeIs('profile.*');
@endphp

<aside
    class="admin-sidebar"
    :class="{ 'admin-sidebar--open': mobileNav }"
    @keydown.escape.window="mobileNav = false"
>
    <div class="admin-sidebar__inner">
        <button type="button" class="admin-sidebar__close" @click="mobileNav = false">
            {{ __('Close menu') }}
        </button>

        <a href="{{ route('dashboard') }}" class="admin-sidebar__brand" @click="mobileNav = false">
            <span class="admin-sidebar__logo-mark">
                <img src="{{ asset('assets/logo-sidebar.svg') }}" alt="{{ config('app.name', 'ATS Analyzer') }}" class="w-full h-full rounded-[inherit] object-cover">
            </span>
            <span class="admin-sidebar__brand-text">
                <span class="admin-sidebar__brand-title">{{ config('app.name', 'ATS Analysis') }}</span>
            </span>
        </a>

        <div class="admin-sidebar__label">{{ __('Menu') }}</div>
        <nav class="admin-sidebar__nav">
            <a href="{{ route('dashboard') }}" class="admin-sidebar__link {{ $isDash ? 'is-active' : '' }}" @click="mobileNav = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('applications.index') }}" class="admin-sidebar__link {{ $isApps ? 'is-active' : '' }}" @click="mobileNav = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2-1.75 2H5.5c-.963 0-1.75-.906-1.75-2v-4.15m16.5 0a2.251 2.251 0 00-1.591-1.591M3.75 14.15v-4.25m0 4.25a2.251 2.251 0 001.591 1.591M20.25 14.15l-3.629-3.629m0 0A2.25 2.25 0 0015.75 9h-1.5a2.25 2.25 0 00-2.25 2.25v.75m7.5 2.25l3.629-3.629m0 0A2.25 2.25 0 0019.5 9h-1.5a2.25 2.25 0 00-2.25 2.25v.75M9 9h3.75M9 12h3m-3 3h3m-6.75-3h6m-6.75 3h6" /></svg>
                {{ __('Applications') }}
            </a>
            @can('use-ats-lab')
                <a href="{{ route('ats.index') }}" class="admin-sidebar__link {{ $isAts ? 'is-active' : '' }}" @click="mobileNav = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v4.125c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 013 17.25v-4.125zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125v-8.25zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v13.5c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    {{ __('ATS') }}
                </a>
            @endcan
            <a href="{{ route('settings.index') }}" class="admin-sidebar__link {{ $isSettings ? 'is-active' : '' }}" @click="mobileNav = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('Settings') }}
            </a>
        </nav>
    </div>
</aside>
