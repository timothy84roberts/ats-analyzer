<x-app-layout>
    @php
        $returnTo = request('return_to', url()->previous());
    @endphp

    <x-slot name="header">
        <div style="display: flex; align-items: center; gap: 16px;">
            <a
                href="{{ $returnTo }}"
                class="admin-btn admin-btn--ghost"
            >
                {{ __('Go back') }}
            </a>
            <h1>{{ __('Edit application') }}</h1>
        </div>
    </x-slot>

    <div class="admin-form-page">
        @if (session('status'))
            <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
        @endif

        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('applications.update', $application) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @include('job-applications._form', ['application' => $application])
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
