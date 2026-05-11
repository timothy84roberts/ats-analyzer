<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Add platform') }}</h1>
    </x-slot>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('platforms.store') }}">
                @csrf
                <div class="admin-form-stack">
                    <div class="admin-field">
                        <label class="admin-label" for="name">{{ __('Name') }}</label>
                        <input id="name" name="name" class="admin-input" type="text" value="{{ old('name') }}" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="slug">{{ __('Slug (URL-safe)') }}</label>
                        <input id="slug" name="slug" class="admin-input" type="text" value="{{ old('slug') }}" required>
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="sort_order">{{ __('Sort order') }}</label>
                        <input id="sort_order" type="number" name="sort_order" class="admin-input" value="{{ old('sort_order', 0) }}">
                        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                    </div>
                    <div class="admin-check">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        <label for="is_active">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
