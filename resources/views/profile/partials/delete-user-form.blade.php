<section>
    <header class="admin-form-section__head">
        <h2 class="admin-form-section__title">{{ __('Delete Account') }}</h2>
        <p class="admin-form-section__desc">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
    </header>

    <button
        type="button"
        class="admin-btn"
        style="background: #dc2626; color: #fff; border-color: transparent;"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="admin-form-section__title">{{ __('Are you sure you want to delete your account?') }}</h2>
            <p class="admin-form-section__desc" style="margin-top: 10px;">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

            <div class="admin-field" style="margin-top: 20px;">
                <label class="admin-label sr-only" for="password">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="admin-input"
                    style="max-width: 320px;"
                    placeholder="{{ __('Password') }}"
                >
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="admin-form-actions" style="margin-top: 24px; justify-content: flex-end;">
                <button type="button" class="admin-btn admin-btn--ghost" x-on:click="$dispatch('close')">{{ __('Cancel') }}</button>
                <button type="submit" class="admin-btn" style="background: #dc2626; color: #fff; border-color: transparent;">{{ __('Delete Account') }}</button>
            </div>
        </form>
    </x-modal>
</section>
