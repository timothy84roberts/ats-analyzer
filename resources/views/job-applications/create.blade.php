<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('New application') }}</h1>
    </x-slot>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('applications.store') }}" enctype="multipart/form-data">
                @csrf
                @include('job-applications._form')
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
