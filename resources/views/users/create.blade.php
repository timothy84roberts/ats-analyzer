<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('users.index') }}" class="admin-link" style="display: inline-block; margin-bottom: 8px; font-size: 0.8125rem;">← {{ __('Users') }}</a>
            <h1 style="margin: 0;">{{ __('Add user') }}</h1>
        </div>
    </x-slot>

    <p class="admin-muted-hint" style="margin: 0 0 20px;">
        {{ __('Managed users appear on job applications and the schedule. They cannot sign in.') }}
    </p>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('users.store') }}">
                @csrf
                @include('users._form')
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
