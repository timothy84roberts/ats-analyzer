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
</x-app-layout>
