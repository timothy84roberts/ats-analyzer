<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row">
            <div>
                <a href="{{ route('settings.index') }}" class="admin-link" style="display: inline-block; margin-bottom: 8px; font-size: 0.8125rem;">← {{ __('Settings') }}</a>
                <h1 style="margin: 0;">{{ __('Platforms') }}</h1>
            </div>
            <a href="{{ route('platforms.create') }}" class="admin-btn admin-btn--primary">{{ __('Add platform') }}</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
    @endif
    @error('delete')
        <div class="admin-alert admin-alert--error">{{ $message }}</div>
    @enderror

    <div class="admin-table-wrap">
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th style="width: 180px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($platforms as $platform)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    @if ($platform->logo_url)
                                        <img
                                            src="{{ $platform->logo_url }}"
                                            alt="{{ $platform->name }} logo"
                                            width="24"
                                            height="24"
                                            loading="lazy"
                                            style="width: 24px; height: 24px; border-radius: 6px; object-fit: contain; flex-shrink: 0; background: #fff; border: 1px solid var(--admin-border, #e5e7eb);"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                        <span style="display: none; width: 24px; height: 24px; border-radius: 6px; flex-shrink: 0; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--admin-accent, #2563eb); background: var(--admin-accent-muted, rgba(37,99,235,0.12));">{{ \Illuminate\Support\Str::substr($platform->name, 0, 1) }}</span>
                                    @else
                                        <span style="display: inline-flex; width: 24px; height: 24px; border-radius: 6px; flex-shrink: 0; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--admin-accent, #2563eb); background: var(--admin-accent-muted, rgba(37,99,235,0.12));">{{ \Illuminate\Support\Str::substr($platform->name, 0, 1) }}</span>
                                    @endif
                                    <span>{{ $platform->name }}</span>
                                </div>
                            </td>
                            <td><code class="admin-code">{{ $platform->slug }}</code></td>
                            <td>
                                <span class="admin-pill {{ $platform->is_active ? 'admin-pill--success' : 'admin-pill--waiting' }}">{{ $platform->is_active ? __('Yes') : __('No') }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="admin-table-actions" style="flex-wrap: nowrap; gap: 8px;">
                                    <a href="{{ route('platforms.edit', $platform) }}" class="admin-btn admin-btn--ghost" style="height: 36px; padding: 0 14px;">{{ __('Edit') }}</a>
                                    <form action="{{ route('platforms.destroy', $platform) }}" method="post" onsubmit="return confirm(@json(__('Delete this platform?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--danger" style="height: 36px; padding: 0 14px;">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            {{ $platforms->links() }}
        </div>
    </div>
</x-app-layout>
