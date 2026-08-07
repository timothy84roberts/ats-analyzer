<x-app-layout>
    <x-slot name="header">
        <div class="admin-page-head__row">
            <div>
                <h1 style="margin: 0;">{{ __('Users') }}</h1>
            </div>
            <a href="{{ route('users.create') }}" class="admin-btn admin-btn--primary">{{ __('Add user') }}</a>
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
                        <th>{{ __('Full name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th style="width: 180px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?: '—' }}</td>
                            <td>
                                @php
                                    $countryName = $user->country_code
                                        ? ($countryNames[$user->country_code] ?? $user->country_code)
                                        : null;
                                    $location = collect([
                                        $user->city,
                                        $user->state,
                                        $countryName,
                                    ])->filter()->implode(', ');
                                @endphp
                                @if ($location !== '')
                                    <span style="display:inline-flex;align-items:center;gap:6px;">
                                        @if ($user->country_code)
                                            <img
                                                src="{{ 'https://flagcdn.com/24x18/'.strtolower($user->country_code).'.png' }}"
                                                alt=""
                                                width="20"
                                                height="14"
                                                style="border-radius:2px; object-fit:cover; flex-shrink:0;"
                                            >
                                        @endif
                                        <span>{{ $location }}</span>
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="admin-table-actions" style="flex-wrap: nowrap; gap: 8px;">
                                    <a href="{{ route('users.edit', $user) }}" class="admin-btn admin-btn--ghost" style="height: 36px; padding: 0 14px;">{{ __('Edit') }}</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="post" onsubmit="return confirm(@json(__('Delete this user?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--danger" style="height: 36px; padding: 0 14px;">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="color: var(--admin-text-muted);">{{ __('No managed users yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
