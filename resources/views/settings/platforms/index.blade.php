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

    <div id="platform-reorder-status" class="admin-alert admin-alert--success" style="display: none;" role="status"></div>
    <div id="platform-reorder-error" class="admin-alert admin-alert--error" style="display: none;" role="alert"></div>

    <div class="admin-table-wrap">
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 44px;" aria-label="{{ __('Reorder') }}"></th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th style="width: 180px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="platform-sortable">
                    @foreach ($platforms as $platform)
                        <tr data-platform-id="{{ $platform->id }}">
                            <td>
                                <button
                                    type="button"
                                    class="platform-drag-handle"
                                    aria-label="{{ __('Drag to reorder') }}"
                                    title="{{ __('Drag to reorder') }}"
                                >
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" d="M9 6h12M9 12h12M9 18h12M3 6h.01M3 12h.01M3 18h.01" />
                                    </svg>
                                </button>
                            </td>
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
    </div>

    @push('styles')
        <style>
            .platform-drag-handle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                padding: 0;
                border: none;
                border-radius: 8px;
                background: transparent;
                color: var(--admin-text-muted);
                cursor: grab;
            }

            .platform-drag-handle:active {
                cursor: grabbing;
            }

            .platform-drag-handle:hover {
                background: var(--admin-accent-muted);
                color: var(--admin-accent);
            }

            #platform-sortable .sortable-ghost {
                opacity: 0.45;
                background: var(--admin-accent-muted);
            }

            #platform-sortable .sortable-drag {
                background: var(--admin-surface);
                box-shadow: var(--admin-shadow);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var tbody = document.getElementById('platform-sortable');
                if (!tbody || typeof Sortable === 'undefined') {
                    return;
                }

                var statusEl = document.getElementById('platform-reorder-status');
                var errorEl = document.getElementById('platform-reorder-error');
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
                var saving = false;

                function hideAlerts() {
                    if (statusEl) {
                        statusEl.style.display = 'none';
                    }
                    if (errorEl) {
                        errorEl.style.display = 'none';
                    }
                }

                function showStatus(message) {
                    hideAlerts();
                    if (!statusEl) {
                        return;
                    }
                    statusEl.textContent = message;
                    statusEl.style.display = 'block';
                }

                function showError(message) {
                    hideAlerts();
                    if (!errorEl) {
                        return;
                    }
                    errorEl.textContent = message;
                    errorEl.style.display = 'block';
                }

                function currentOrder() {
                    return Array.from(tbody.querySelectorAll('tr[data-platform-id]')).map(function (row) {
                        return parseInt(row.getAttribute('data-platform-id'), 10);
                    });
                }

                function saveOrder() {
                    if (saving) {
                        return;
                    }

                    saving = true;
                    hideAlerts();

                    fetch(@json(route('platforms.reorder')), {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ order: currentOrder() }),
                    })
                        .then(function (res) {
                            return res.json().then(function (data) {
                                return { res: res, data: data };
                            });
                        })
                        .then(function (payload) {
                            if (!payload.res.ok) {
                                throw new Error(payload.data.message || @json(__('Could not save platform order.')));
                            }

                            showStatus(@json(__('Platform order saved.')));
                        })
                        .catch(function (err) {
                            showError(err.message || @json(__('Could not save platform order.')));
                        })
                        .finally(function () {
                            saving = false;
                        });
                }

                Sortable.create(tbody, {
                    animation: 150,
                    handle: '.platform-drag-handle',
                    draggable: 'tr[data-platform-id]',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: saveOrder,
                });
            });
        </script>
    @endpush
</x-app-layout>
