<section>
    <header class="admin-form-section__head">
        <h2 class="admin-form-section__title">{{ __('Profile Information') }}</h2>
        <p class="admin-form-section__desc">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="admin-form-stack">
        @csrf
        @method('patch')

        <div class="admin-field">
            <label class="admin-label" for="name">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="admin-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="admin-field">
            <label class="admin-label" for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="admin-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="admin-muted-hint">
                    <p>{{ __('Your email address is unverified.') }}</p>
                    <button type="submit" form="send-verification" class="admin-link">{{ __('Click here to re-send the verification email.') }}</button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="admin-alert admin-alert--success" style="margin-top: 12px;">{{ __('A new verification link has been sent to your email address.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save') }}</button>
            @if (session('status') === 'profile-updated')
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
