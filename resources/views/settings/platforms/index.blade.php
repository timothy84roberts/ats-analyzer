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
                        <th>{{ __('Sort') }}</th>
                        <th style="width: 140px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($platforms as $platform)
                        <tr>
                            <td>{{ $platform->name }}</td>
                            <td><code class="admin-code">{{ $platform->slug }}</code></td>
                            <td>
                                <span class="admin-pill {{ $platform->is_active ? 'admin-pill--success' : 'admin-pill--waiting' }}">{{ $platform->is_active ? __('Yes') : __('No') }}</span>
                            </td>
                            <td>{{ $platform->sort_order }}</td>
                            <td class="whitespace-nowrap">
                                <div class="admin-table-actions">
                                    <a href="{{ route('platforms.edit', $platform) }}" class="admin-btn admin-btn--ghost" style="height: 36px; padding: 0 14px;">{{ __('Edit') }}</a>
                                    <form action="{{ route('platforms.destroy', $platform) }}" method="post" onsubmit="return confirm(@json(__('Delete this platform?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--danger">{{ __('Delete') }}</button>
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
