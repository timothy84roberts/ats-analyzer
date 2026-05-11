@php
    $application = $application ?? null;
    $editing = (bool) ($application?->exists ?? false);
    $defaultOutcome = \App\Models\JobApplication::OUTCOME_WAITING;
    $rejectedOutcome = \App\Models\JobApplication::OUTCOME_REJECTED;
@endphp
<div class="admin-form-stack" @if ($editing) x-data="{ outcome: @js(old('outcome_status', $application?->outcome_status ?? $defaultOutcome)) }" @endif>
    @unless ($editing)
        <p class="admin-muted-hint" style="margin: 0;">
            {{ __('New applications start in Waiting at the first pipeline stage. After the company responds, use Edit to change pipeline stage, set outcome to Rejected if they declined, and enter a rejection reason.') }}
        </p>
    @endunless

    <div class="admin-field">
        <label class="admin-label" for="title">{{ __('Job title') }}</label>
        <input id="title" class="admin-input" type="text" name="title" value="{{ old('title', $application?->title ?? '') }}" required @if(! $editing) autofocus @endif>
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>
    <div class="admin-field">
        <label class="admin-label" for="description">{{ __('Description') }}</label>
        <div
            class="admin-summernote-wrap"
            id="description-editor-wrap"
            data-placeholder="{{ __('Formatting, lists, and links are supported.') }}"
        >
            <textarea id="description" name="description" rows="6" class="w-full">{!! \App\Support\RichTextSanitizer::sanitize(old('description', $application?->description ?? '')) !!}</textarea>
        </div>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="admin-field">
        <label class="admin-label" for="resume">{{ __('Resume (PDF)') }}</label>
        @if ($editing && $application?->hasResume())
            <p class="admin-muted-hint" style="margin: 0 0 8px;">
                {{ __('A file is already uploaded. Choose a new PDF to replace it, or check “remove” below to delete it.') }}
            </p>
        @else
            <p class="admin-muted-hint" style="margin: 0 0 8px;">{{ __('Optional. PDF only, max 10 MB.') }}</p>
        @endif
        <div class="admin-file-upload">
            <label class="admin-file-upload__btn-wrap">
                <span class="admin-btn admin-btn--primary">{{ __('Choose PDF') }}</span>
                <input
                    id="resume"
                    type="file"
                    name="resume"
                    class="admin-file-upload__input"
                    accept="application/pdf,.pdf"
                >
            </label>
            <span
                id="resume-file-name"
                class="admin-file-upload__filename"
                data-empty="{{ __('No file chosen') }}"
            >
                @if ($editing && $application?->hasResume())
                    {{ __('PDF on file — choose a new file to replace') }}
                @else
                    {{ __('No file chosen') }}
                @endif
            </span>
        </div>
        <x-input-error :messages="$errors->get('resume')" class="mt-2" />
        @if ($editing && $application?->hasResume())
            <label class="admin-check" style="margin-top: 10px;">
                <input type="checkbox" name="remove_resume" value="1" @checked(old('remove_resume'))>
                <span>{{ __('Remove current resume file') }}</span>
            </label>
            <x-input-error :messages="$errors->get('remove_resume')" class="mt-2" />
        @endif
    </div>

    <div class="admin-field">
        <label class="admin-label" for="notes">{{ __('Notes') }}</label>
        <p class="admin-muted-hint" style="margin: 0 0 8px;">{{ __('Call reservation, skill test details, follow-ups, reminders…') }}</p>
        <textarea id="notes" name="notes" rows="4" class="admin-textarea" placeholder="{{ __('Type any notes you want to keep for this application.') }}">{{ old('notes', $application?->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    @pushOnce('styles')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
        <link href="{{ asset('css/summernote-admin.css') }}" rel="stylesheet">
    @endPushOnce

    @pushOnce('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var ta = document.getElementById('description');
                var wrap = document.getElementById('description-editor-wrap');
                if (!ta || typeof jQuery === 'undefined' || !jQuery.fn.summernote) {
                    return;
                }
                var $ = jQuery;
                var ph = wrap && wrap.getAttribute('data-placeholder') ? wrap.getAttribute('data-placeholder') : '';
                $(ta).summernote({
                    placeholder: ph,
                    height: 260,
                    disableDragAndDrop: true,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview']],
                    ],
                });
                var resumeInput = document.getElementById('resume');
                var resumeNameEl = document.getElementById('resume-file-name');
                if (resumeInput && resumeNameEl) {
                    var emptyLabel = resumeNameEl.getAttribute('data-empty') || '';
                    resumeInput.addEventListener('change', function () {
                        if (this.files && this.files[0]) {
                            resumeNameEl.textContent = this.files[0].name;
                        } else {
                            resumeNameEl.textContent = emptyLabel;
                        }
                    });
                }
            });
        </script>
    @endPushOnce

    @if ($editing)
        <div class="admin-field-grid-2">
            <div class="admin-field">
                <label class="admin-label" for="pipeline_stage_id">{{ __('Pipeline stage') }}</label>
                <select id="pipeline_stage_id" name="pipeline_stage_id" class="admin-select admin-select--emphasis" required>
                    @foreach ($pipelineStages as $stage)
                        <option value="{{ $stage->id }}" @selected((string) old('pipeline_stage_id', $application?->pipeline_stage_id ?? '') === (string) $stage->id)>{{ $stage->label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('pipeline_stage_id')" class="mt-2" />
            </div>
            <div class="admin-field">
                <label class="admin-label" for="outcome_status">{{ __('Outcome status') }}</label>
                <select id="outcome_status" name="outcome_status" x-model="outcome" class="admin-select admin-select--emphasis" required>
                    @foreach ($outcomeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('outcome_status', $application?->outcome_status ?? $defaultOutcome) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('outcome_status')" class="mt-2" />
            </div>
        </div>
        <div class="admin-field" x-show="outcome === '{{ $rejectedOutcome }}'" x-cloak>
            <label class="admin-label" for="rejection_reason">{{ __('Rejection reason') }}</label>
            <textarea id="rejection_reason" name="rejection_reason" rows="4" class="admin-textarea" placeholder="{{ __('Required when outcome is Rejected (e.g. company declined).') }}">{{ old('rejection_reason', $application?->rejection_reason ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
        </div>
    @endif

    <div class="admin-field-grid-2">
        <div class="admin-field">
            <label class="admin-label" for="country_id">{{ __('Country') }}</label>
            <select id="country_id" name="country_id" class="admin-select" required>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $application?->country_id ?? '') === (string) $country->id)>{{ $country->name }} ({{ $country->code }})</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('country_id')" class="mt-2" />
        </div>
        <div class="admin-field">
            <label class="admin-label" for="platform_id">{{ __('Platform') }}</label>
            <select id="platform_id" name="platform_id" class="admin-select" required>
                @foreach ($platforms as $platform)
                    <option value="{{ $platform->id }}" @selected((string) old('platform_id', $application?->platform_id ?? '') === (string) $platform->id)>{{ $platform->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('platform_id')" class="mt-2" />
        </div>
    </div>
    <div class="admin-field">
        <label class="admin-label" for="company_name">{{ __('Company') }}</label>
        <input id="company_name" class="admin-input" type="text" name="company_name" value="{{ old('company_name', $application?->company_name ?? '') }}">
        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
    </div>
    <div class="admin-field-grid-2">
        <div class="admin-field">
            <label class="admin-label" for="applied_on">{{ __('Applied on') }}</label>
            <input id="applied_on" class="admin-input" type="date" name="applied_on" value="{{ old('applied_on', $application?->applied_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            <x-input-error :messages="$errors->get('applied_on')" class="mt-2" />
        </div>
        <div class="admin-field">
            <label class="admin-label" for="analysis_percentage">{{ __('Analysis % (optional)') }}</label>
            <input id="analysis_percentage" class="admin-input" type="number" step="0.01" min="0" max="100" name="analysis_percentage" value="{{ old('analysis_percentage', $application?->analysis_percentage ?? '') }}">
            <x-input-error :messages="$errors->get('analysis_percentage')" class="mt-2" />
        </div>
    </div>
</div>
