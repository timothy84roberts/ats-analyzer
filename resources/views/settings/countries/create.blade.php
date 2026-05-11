<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('countries.index') }}" class="admin-link" style="display: inline-block; margin-bottom: 8px; font-size: 0.8125rem;">← {{ __('Countries') }}</a>
            <h1 style="margin: 0;">{{ __('Add country') }}</h1>
        </div>
    </x-slot>

    <p class="admin-muted-hint" style="margin: 0 0 20px;">
        {{ __('Choose a country from the public REST Countries catalog. Names and ISO codes come from the API; you cannot edit them after adding.') }}
    </p>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('countries.store') }}">
                @csrf
                <div class="admin-form-stack">
                    <div class="admin-field">
                        <label class="admin-label" for="code">{{ __('Country') }}</label>
                        <div class="admin-select-scroll">
                            <select id="code" name="code" class="admin-select" required>
                                <option value="" disabled @selected(! old('code'))>{{ __('Select a country…') }}</option>
                                @foreach ($options as $o)
                                    <option value="{{ $o['code'] }}" @selected(old('code') === $o['code'])>{{ $o['name'] }} ({{ $o['code'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>
                </div>
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Add') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
