<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('countries.index') }}" class="admin-link" style="display: inline-block; margin-bottom: 8px; font-size: 0.8125rem;">← {{ __('Countries') }}</a>
            <h1 style="margin: 0;">{{ __('Add country') }}</h1>
        </div>
    </x-slot>

    <p class="admin-muted-hint" style="margin: 0 0 20px;">
        {{ __('Choose a country from the ISO catalog. Names and ISO codes are fixed after adding.') }}
    </p>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('countries.store') }}">
                @csrf
                <div class="admin-form-stack" x-data="countryPicker(@js($options), @js(old('code')))">
                    <div class="admin-field">
                        <label class="admin-label" for="country-search">{{ __('Search country') }}</label>
                        <input
                            id="country-search"
                            type="text"
                            class="admin-input"
                            placeholder="{{ __('Type country name or ISO code…') }}"
                            autocomplete="off"
                            x-model="query"
                        >
                    </div>

                    <div class="admin-field">
                        <label class="admin-label" for="code">{{ __('Country') }}</label>
                        <input type="hidden" id="code" name="code" x-model="selectedCode">
                        <div style="border: 1px solid var(--admin-border); border-radius: var(--admin-radius-sm); background: var(--admin-surface-2); max-height: 360px; overflow: auto;">
                            <template x-if="filtered().length === 0">
                                <div style="padding: 12px 14px; color: var(--admin-text-muted);">
                                    {{ __('No countries match your search.') }}
                                </div>
                            </template>
                            <template x-for="country in filtered()" :key="country.code">
                                <button
                                    type="button"
                                    @click="selectedCode = country.code"
                                    :style="selectedCode === country.code
                                        ? 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 14px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;border-bottom:1px solid var(--admin-border);text-align:left;cursor:pointer;'
                                        : 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 14px;background:transparent;color:var(--admin-text);border:none;border-bottom:1px solid var(--admin-border);text-align:left;cursor:pointer;'"
                                >
                                    <img
                                        :src="flagUrl(country.code)"
                                        :alt="country.name + ' flag'"
                                        width="20"
                                        height="14"
                                        style="border-radius: 2px; object-fit: cover; flex-shrink: 0;"
                                    >
                                    <span x-text="country.name" style="flex: 1; min-width: 0;"></span>
                                    <span x-text="'(' + country.code + ')'" style="color: var(--admin-text-muted); font-size: 0.8125rem;"></span>
                                </button>
                            </template>
                        </div>
                        <p class="admin-muted-hint" style="margin: 8px 0 0;">
                            <span x-show="selectedCode">{{ __('Selected') }}: <strong x-text="selectedCode"></strong></span>
                            <span x-show="!selectedCode">{{ __('Select a country from the list.') }}</span>
                        </p>
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>
                </div>
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Add') }}</button>
                </div>
            </form>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            function countryPicker(options, initialCode) {
                return {
                    query: '',
                    selectedCode: initialCode || '',
                    options: options || [],
                    filtered: function () {
                        var q = (this.query || '').trim().toLowerCase();
                        if (!q) {
                            return this.options;
                        }
                        return this.options.filter(function (row) {
                            return (row.name + ' ' + row.code).toLowerCase().indexOf(q) !== -1;
                        });
                    },
                    flagUrl: function (code) {
                        return 'https://flagcdn.com/24x18/' + String(code || '').toLowerCase() + '.png';
                    }
                };
            }
        </script>
    @endPushOnce
</x-app-layout>
