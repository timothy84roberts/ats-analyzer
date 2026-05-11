<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row">
            <div>
                <a href="{{ route('settings.index') }}" class="admin-link" style="display: inline-block; margin-bottom: 8px; font-size: 0.8125rem;">← {{ __('Settings') }}</a>
                <h1 style="margin: 0;">{{ __('Countries') }}</h1>
            </div>
            <a href="{{ route('countries.create') }}" class="admin-btn admin-btn--primary">{{ __('Add country') }}</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
    @endif
    @error('catalog')
        <div class="admin-alert admin-alert--error">{{ $message }}</div>
    @enderror
    @error('delete')
        <div class="admin-alert admin-alert--error">{{ $message }}</div>
    @enderror

    <div class="admin-table-wrap">
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th>{{ __('UN numeric') }}</th>
                        <th style="width: 100px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($countries as $country)
                        <tr>
                            <td>{{ $country->name }}</td>
                            <td>{{ $country->code }}</td>
                            <td>
                                <span class="admin-pill {{ $country->is_active ? 'admin-pill--success' : 'admin-pill--waiting' }}">{{ $country->is_active ? __('Yes') : __('No') }}</span>
                            </td>
                            <td>{{ $country->sort_order }}</td>
                            <td class="whitespace-nowrap">
                                <form action="{{ route('countries.destroy', $country) }}" method="post" onsubmit="return confirm(@json(__('Delete this country?')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn--danger">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            {{ $countries->links() }}
        </div>
    </div>
</x-app-layout>
