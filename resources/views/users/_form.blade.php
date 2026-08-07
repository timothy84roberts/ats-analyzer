@php
    $user = $user ?? null;
    $selectedCountryCode = (string) old('country_code', $user?->country_code ?? '');
@endphp
@include('job-applications._pickers')

<div class="admin-form-stack">
    <div class="admin-field">
        <label class="admin-label" for="name">{{ __('Full name') }}</label>
        <input id="name" name="name" class="admin-input" type="text" value="{{ old('name', $user?->name) }}" required autofocus>
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div class="admin-field">
        <label class="admin-label" for="email">{{ __('Email') }}</label>
        <input id="email" name="email" class="admin-input" type="email" value="{{ old('email', $user?->email) }}" required>
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
    <div class="admin-field">
        <label class="admin-label" for="phone">{{ __('Phone') }}</label>
        <input id="phone" name="phone" class="admin-input" type="text" value="{{ old('phone', $user?->phone) }}" autocomplete="tel">
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>
    <div class="admin-field">
        <label class="admin-label" for="address">{{ __('Address') }}</label>
        <input id="address" name="address" class="admin-input" type="text" value="{{ old('address', $user?->address) }}">
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>
    <div class="admin-field" x-data="countryPicker(@js($countryOptions), @js($selectedCountryCode))">
        <label class="admin-label" for="country_code">{{ __('Country') }}</label>
        <input id="country_code" type="hidden" name="country_code" x-model="selectedId">
        <div style="position: relative;">
            <button
                type="button"
                class="admin-select"
                @click="open = !open"
                style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;"
            >
                <span style="display:flex; align-items:center; gap:10px; min-width:0;">
                    <template x-if="selected()">
                        <img :src="selected().flag" :alt="selected().name + ' flag'" width="20" height="14" style="border-radius:2px; object-fit:cover; flex-shrink:0;">
                    </template>
                    <span x-show="selected()" x-text="selected() ? (selected().name + ' (' + selected().code + ')') : ''" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                    <span x-show="!selected()" style="color: var(--admin-text-muted);">{{ __('Select country') }}</span>
                </span>
                <span style="color: var(--admin-text-muted);">▾</span>
            </button>
            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:240px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);"
            >
                <button
                    type="button"
                    @click="pick('')"
                    :style="selectedId === ''
                        ? 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;'
                        : 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'"
                >
                    <span style="color: var(--admin-text-muted);">{{ __('None') }}</span>
                </button>
                <template x-for="country in options" :key="country.id">
                    <button
                        type="button"
                        @click="pick(country.id)"
                        :style="selectedId === country.id
                            ? 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;'
                            : 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'"
                    >
                        <img :src="country.flag" :alt="country.name + ' flag'" width="20" height="14" style="border-radius:2px; object-fit:cover; flex-shrink:0;">
                        <span x-text="country.name + ' (' + country.code + ')'"></span>
                    </button>
                </template>
            </div>
        </div>
        <x-input-error :messages="$errors->get('country_code')" class="mt-2" />
    </div>
    <div class="admin-field-grid-2">
        <div class="admin-field">
            <label class="admin-label" for="city">{{ __('City') }}</label>
            <input id="city" name="city" class="admin-input" type="text" value="{{ old('city', $user?->city) }}">
            <x-input-error :messages="$errors->get('city')" class="mt-2" />
        </div>
        <div class="admin-field">
            <label class="admin-label" for="state">{{ __('State / Province') }}</label>
            <input id="state" name="state" class="admin-input" type="text" value="{{ old('state', $user?->state) }}" placeholder="{{ __('Optional') }}">
            <x-input-error :messages="$errors->get('state')" class="mt-2" />
        </div>
    </div>
    <div class="admin-field">
        <label class="admin-label" for="birthday">{{ __('Birthday') }}</label>
        <input id="birthday" name="birthday" class="admin-input" type="date" value="{{ old('birthday', $user?->birthday?->format('Y-m-d')) }}">
        <x-input-error :messages="$errors->get('birthday')" class="mt-2" />
    </div>

    <div class="admin-social-profiles">
        <div class="admin-social-profiles__title">{{ __('Social profiles') }}</div>
        <div class="admin-social-profiles__stack">
            <div class="admin-field">
                <label class="admin-label" for="linkedin" style="display:inline-flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="color:#0A66C2;"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    <span>{{ __('LinkedIn') }}</span>
                </label>
                <input id="linkedin" name="linkedin" class="admin-input" type="text" value="{{ old('linkedin', $user?->linkedin) }}" placeholder="https://linkedin.com/in/…">
                <x-input-error :messages="$errors->get('linkedin')" class="mt-2" />
            </div>
            <div class="admin-field">
                <label class="admin-label" for="github" style="display:inline-flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="color:var(--admin-text);"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                    <span>{{ __('GitHub') }}</span>
                </label>
                <input id="github" name="github" class="admin-input" type="text" value="{{ old('github', $user?->github) }}" placeholder="https://github.com/…">
                <x-input-error :messages="$errors->get('github')" class="mt-2" />
            </div>
            <div class="admin-field">
                <label class="admin-label" for="x_url" style="display:inline-flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="color:var(--admin-text);"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.227-8.451L1.502 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
                    <span>{{ __('X') }}</span>
                </label>
                <input id="x_url" name="x_url" class="admin-input" type="text" value="{{ old('x_url', $user?->x_url) }}" placeholder="https://x.com/…">
                <x-input-error :messages="$errors->get('x_url')" class="mt-2" />
            </div>
            <div class="admin-field">
                <label class="admin-label" for="facebook" style="display:inline-flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="color:#1877F2;"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                    <span>{{ __('Facebook') }}</span>
                </label>
                <input id="facebook" name="facebook" class="admin-input" type="text" value="{{ old('facebook', $user?->facebook) }}" placeholder="https://facebook.com/…">
                <x-input-error :messages="$errors->get('facebook')" class="mt-2" />
            </div>
            <div class="admin-field">
                <label class="admin-label" for="instagram" style="display:inline-flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="color:#E4405F;"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    <span>{{ __('Instagram') }}</span>
                </label>
                <input id="instagram" name="instagram" class="admin-input" type="text" value="{{ old('instagram', $user?->instagram) }}" placeholder="https://instagram.com/…">
                <x-input-error :messages="$errors->get('instagram')" class="mt-2" />
            </div>
            <div class="admin-field">
                <label class="admin-label" for="website" style="display:inline-flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" style="color:var(--admin-accent);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>
                    <span>{{ __('Website') }}</span>
                </label>
                <input id="website" name="website" class="admin-input" type="text" value="{{ old('website', $user?->website) }}" placeholder="https://…">
                <x-input-error :messages="$errors->get('website')" class="mt-2" />
            </div>
        </div>
    </div>
</div>

@pushOnce('styles')
    <style>
        .admin-social-profiles {
            margin-top: 4px;
            padding-top: 20px;
            border-top: 1px solid var(--admin-border);
        }
        .admin-social-profiles__title {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--admin-text);
            margin-bottom: 18px;
        }
        .admin-social-profiles__stack {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }
    </style>
@endPushOnce
