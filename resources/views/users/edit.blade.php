<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('users.index') }}" class="admin-link" style="display: inline-block; margin-bottom: 8px; font-size: 0.8125rem;">← {{ __('Users') }}</a>
            <h1 style="margin: 0;">{{ __('Edit user') }}</h1>
        </div>
    </x-slot>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('users._form', ['user' => $user])
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
