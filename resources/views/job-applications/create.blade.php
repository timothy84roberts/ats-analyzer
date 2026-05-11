<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('New application') }}</h1>
    </x-slot>

    <div class="admin-form-page">
        @if (session('status'))
            <div class="admin-alert admin-alert--success js-auto-dismiss-alert">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert--error js-auto-dismiss-alert">
                {{ __('Failed to create application. Please review the form and try again.') }}
            </div>
        @endif

        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('applications.store') }}" enctype="multipart/form-data">
                @csrf
                @include('job-applications._form')
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <label class="admin-check" style="margin-right: auto;">
                        <input type="hidden" name="keep_creating" value="0">
                        <input id="keep_creating" type="checkbox" name="keep_creating" value="1" @checked(old('keep_creating'))>
                        <span>{{ __('Keep continue') }}</span>
                    </label>
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.js-auto-dismiss-alert').forEach(function (alertEl) {
                    setTimeout(function () {
                        alertEl.style.transition = 'opacity 0.25s ease';
                        alertEl.style.opacity = '0';
                        setTimeout(function () {
                            alertEl.remove();
                        }, 250);
                    }, 5000);
                });
            });
        </script>
    @endPushOnce
</x-app-layout>
