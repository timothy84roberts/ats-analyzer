<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('settings.index') }}" class="admin-link" style="display: inline-block; margin-bottom: 8px; font-size: 0.8125rem;">← {{ __('Settings') }}</a>
            <h1 style="margin: 0;">{{ __('Profile') }}</h1>
        </div>
    </x-slot>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="admin-card admin-card--pad" style="margin-top: 20px;">
            @include('profile.partials.update-password-form')
        </div>

        <div class="admin-card admin-card--pad" style="margin-top: 20px;">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
