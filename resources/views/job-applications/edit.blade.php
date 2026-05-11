<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('Edit application') }}</h1>
    </x-slot>

    <div class="admin-form-page">
        <div class="admin-card admin-card--pad">
            <form method="post" action="{{ route('applications.update', $application) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('job-applications._form', ['application' => $application])
                <div class="admin-form-actions" style="margin-top: 24px;">
                    <button type="submit" class="admin-btn admin-btn--primary">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
