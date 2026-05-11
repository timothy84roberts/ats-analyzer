<x-app-layout>
    <x-slot name="header">
        <h1>{{ __('ATS analysis (testing)') }}</h1>
    </x-slot>

    <div class="admin-alert admin-alert--warn" style="margin-bottom: 20px;">
        {{ __('This module is in testing. Resume parsing and scoring are not wired yet. Use job applications to track outcomes and analysis percentage manually if needed.') }}
    </div>

    <div class="admin-card admin-card--pad">
        <p class="admin-form-section__desc" style="margin-top: 0;">{{ __('Planned capabilities:') }}</p>
        <ul style="margin: 12px 0 0; padding-left: 1.25rem; color: var(--admin-text); font-size: 0.875rem; line-height: 1.65;">
            <li>{{ __('Upload resume and match against job description') }}</li>
            <li>{{ __('Persist runs in') }} <code class="admin-code">ats_analysis_runs</code></li>
            <li>{{ __('Optional sync to') }} <code class="admin-code">job_applications.analysis_percentage</code></li>
        </ul>
    </div>
</x-app-layout>
