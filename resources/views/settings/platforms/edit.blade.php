<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Edit platform') }}</h1>
    </x-slot>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('platforms.update', $platform) }}">
                @csrf
                @method('PUT')
                <div class="admin-form-stack">
                    <div class="admin-field">
                        <label class="admin-label" for="name">{{ __('Name') }}</label>
                        <input id="name" name="name" class="admin-input" type="text" value="{{ old('name', $platform->name) }}" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="slug">{{ __('Slug') }}</label>
                        <input id="slug" name="slug" class="admin-input" type="text" value="{{ old('slug', $platform->slug) }}" required>
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="url">{{ __('Website URL') }}</label>
                        <input id="url" name="url" class="admin-input" type="url" value="{{ old('url', $platform->url) }}" placeholder="https://www.linkedin.com">
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">{{ __('Used to display the platform logo.') }}</span>
                        <x-input-error :messages="$errors->get('url')" class="mt-2" />
                    </div>
                    <div class="admin-check">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $platform->is_active))>
                        <label for="is_active">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
