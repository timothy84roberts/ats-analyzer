<section>
    <header class="admin-form-section__head">
        <h2 class="admin-form-section__title">{{ __('Update Password') }}</h2>
        <p class="admin-form-section__desc">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="admin-form-stack">
        @csrf
        @method('put')

        <div class="admin-field">
            <label class="admin-label" for="update_password_current_password">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="admin-input" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="admin-field">
            <label class="admin-label" for="update_password_password">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" class="admin-input" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="admin-field">
            <label class="admin-label" for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="admin-input" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save') }}</button>
            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="admin-muted-hint"
                    style="margin: 0;"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
