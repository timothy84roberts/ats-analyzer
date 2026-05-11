<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo-mark.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <script>try{var t=localStorage.getItem('ats-theme');document.documentElement.dataset.theme=t==='dark'?'dark':'light';}catch(e){document.documentElement.dataset.theme='light';}</script>
        <link href="{{ asset('css/compiled.css') }}" rel="stylesheet">
        <link href="{{ asset('css/theme-overrides.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin-shell.css') }}" rel="stylesheet">
        @stack('styles')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
        <script defer src="{{ asset('js/theme.js') }}"></script>
    </head>
    <body class="admin-body font-sans antialiased">
        <div class="admin-layout" x-data="{ mobileNav: false }">
            @include('layouts.admin-sidebar')

            <div
                class="admin-backdrop"
                x-show="mobileNav"
                x-transition.opacity
                x-cloak
                @click="mobileNav = false"
            ></div>

            <div class="admin-main">
                @include('layouts.admin-topbar')

                <main class="admin-content">
                    @isset($header)
                        <div class="admin-page-head">
                            {{ $header }}
                        </div>
                    @endisset

                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
