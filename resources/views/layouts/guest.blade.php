<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <script>try{var t=localStorage.getItem('ats-theme');document.documentElement.dataset.theme=t==='dark'?'dark':'light';}catch(e){document.documentElement.dataset.theme='light';}</script>
        <link href="{{ asset('css/compiled.css') }}" rel="stylesheet">
        <link href="{{ asset('css/theme-overrides.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin-shell.css') }}" rel="stylesheet">
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
        <script defer src="{{ asset('js/theme.js') }}"></script>
    </head>
    <body class="admin-auth-body">
        <div class="admin-auth-logo">
            <a href="/" class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold text-xl shadow-md">
                {{ strtoupper(substr(config('app.name', 'L'), 0, 1)) }}
            </a>
        </div>
        <div class="admin-auth-card">
            {{ $slot }}
        </div>
    </body>
</html>
