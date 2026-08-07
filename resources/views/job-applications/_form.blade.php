@php
    $application = $application ?? null;
    $editing = (bool) ($application?->exists ?? false);
    $defaultOutcome = \App\Models\JobApplication::OUTCOME_WAITING;
    $rejectedOutcome = \App\Models\JobApplication::OUTCOME_REJECTED;
    $countryOptions = $countries->map(fn ($country) => [
        'id' => (string) $country->id,
        'name' => $country->name,
        'code' => strtoupper($country->code),
        'flag' => 'https://flagcdn.com/24x18/'.strtolower($country->code).'.png',
    ])->values();
    $selectedCountryId = (string) old('country_id', $application?->country_id ?? '');
    $platformOptions = $platforms->map(fn ($platform) => [
        'id' => (string) $platform->id,
        'name' => $platform->name,
        'logo' => $platform->logo_url,
        'initial' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($platform->name, 0, 1)),
    ])->values();
    $selectedPlatformId = (string) old('platform_id', $application?->platform_id ?? '');
    $defaultAppliedOn = old('applied_on', $application?->applied_on?->format('Y-m-d') ?? now()->format('Y-m-d'));
    $selectedUserId = (string) old('user_id', $application?->user_id ?? '');
@endphp
<div class="admin-form-stack" @if ($editing) x-data="{ outcome: @js(old('outcome_status', $application?->outcome_status ?? $defaultOutcome)), outcomeOpen: false, stageId: @js((string) old('pipeline_stage_id', $application?->pipeline_stage_id ?? '')), stageOpen: false }" @endif>
    <div class="admin-field">
        <label class="admin-label" for="title">{{ __('Job title') }}</label>
        <input id="title" class="admin-input" type="text" name="title" value="{{ old('title', $application?->title ?? '') }}" required autofocus>
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

    @pushOnce('styles')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
        <link href="{{ asset('css/summernote-admin.css') }}" rel="stylesheet">
    @endPushOnce

    @include('job-applications._pickers')

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
                <label class="admin-label">{{ __('Pipeline stage') }}</label>
                <input type="hidden" name="pipeline_stage_id" :value="stageId">
                <div style="position: relative;">
                    <button type="button" class="admin-select admin-select--emphasis" @click="stageOpen = !stageOpen" style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;">
                        <span style="display:flex; align-items:center; gap:8px; min-width:0;">
                            @foreach ($pipelineStages as $stage)
                                <span style="display:none;" :style="stageId === '{{ $stage->id }}' ? 'display:inline-flex;align-items:center;gap:8px;' : 'display:none'">
                                    <x-stage-icon :slug="$stage->slug" :size="16" />
                                    <span>{{ $stage->label }}</span>
                                </span>
                            @endforeach
                        </span>
                        <span style="color: var(--admin-text-muted);">▾</span>
                    </button>
                    <div x-show="stageOpen" x-cloak @click.outside="stageOpen = false" style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:240px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);">
                        @foreach ($pipelineStages as $stage)
                            <button type="button" @click="stageId = '{{ $stage->id }}'; stageOpen = false" :style="stageId === '{{ $stage->id }}' ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                                <x-stage-icon :slug="$stage->slug" :size="16" />
                                <span>{{ $stage->label }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <x-input-error :messages="$errors->get('pipeline_stage_id')" class="mt-2" />
            </div>
            <div class="admin-field">
                <label class="admin-label">{{ __('Outcome status') }}</label>
                <input type="hidden" name="outcome_status" :value="outcome">
                <div style="position: relative;">
                    <button type="button" class="admin-select admin-select--emphasis" @click="outcomeOpen = !outcomeOpen" style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;">
                        <span style="display:flex; align-items:center; gap:8px; min-width:0;">
                            @foreach ($outcomeOptions as $value => $label)
                                <span style="display:none;" :style="outcome === '{{ $value }}' ? 'display:inline-flex;align-items:center;gap:8px;' : 'display:none'">
                                    <x-outcome-icon :status="$value" :size="16" />
                                    <span>{{ $label }}</span>
                                </span>
                            @endforeach
                        </span>
                        <span style="color: var(--admin-text-muted);">▾</span>
                    </button>
                    <div x-show="outcomeOpen" x-cloak @click.outside="outcomeOpen = false" style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:240px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);">
                        @foreach ($outcomeOptions as $value => $label)
                            <button type="button" @click="outcome = '{{ $value }}'; outcomeOpen = false" :style="outcome === '{{ $value }}' ? 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;' : 'display:flex;width:100%;align-items:center;gap:8px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'">
                                <x-outcome-icon :status="$value" :size="16" />
                                <span>{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
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
        <div class="admin-field" x-data="countryPicker(@js($countryOptions), @js($selectedCountryId))">
            <label class="admin-label" for="country_id">{{ __('Country') }}</label>
            <input id="country_id" type="hidden" name="country_id" x-model="selectedId" required>
            <div style="position: relative;">
                <button
                    type="button"
                    class="admin-select"
                    @click="open = !open"
                    style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;"
                >
                    <span style="display:flex; align-items:center; gap:10px; min-width:0;">
                        <template x-if="selected()">
                            <img :src="selected().flag" :alt="selected().name + ' flag'" width="20" height="14" style="border-radius:2px; object-fit:cover; flex-shrink:0;">
                        </template>
                        <span x-show="selected()" x-text="selected() ? (selected().name + ' (' + selected().code + ')') : ''" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        <span x-show="!selected()" style="color: var(--admin-text-muted);">{{ __('Select country') }}</span>
                    </span>
                    <span style="color: var(--admin-text-muted);">▾</span>
                </button>
                <div
                    x-show="open"
                    x-cloak
                    @click.outside="open = false"
                    style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:180px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);"
                >
                    <template x-for="country in options" :key="country.id">
                        <button
                            type="button"
                            @click="pick(country.id)"
                            :style="selectedId === country.id
                                ? 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;'
                                : 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'"
                        >
                            <img :src="country.flag" :alt="country.name + ' flag'" width="20" height="14" style="border-radius:2px; object-fit:cover; flex-shrink:0;">
                            <span x-text="country.name + ' (' + country.code + ')'"></span>
                        </button>
                    </template>
                </div>
            </div>
            <x-input-error :messages="$errors->get('country_id')" class="mt-2" />
        </div>
        <div class="admin-field" x-data="platformPicker(@js($platformOptions), @js($selectedPlatformId))">
            <label class="admin-label" for="platform_id">{{ __('Platform') }}</label>
            <input id="platform_id" type="hidden" name="platform_id" x-model="selectedId" required>
            <div style="position: relative;">
                <button
                    type="button"
                    class="admin-select"
                    @click="open = !open"
                    style="display:flex; align-items:center; justify-content:space-between; gap:10px; text-align:left;"
                >
                    <span style="display:flex; align-items:center; gap:10px; min-width:0;">
                        <template x-if="selected() && selected().logo">
                            <img :src="selected().logo" :alt="selected().name + ' logo'" width="20" height="20" style="border-radius:4px; object-fit:contain; flex-shrink:0; background:#fff;">
                        </template>
                        <template x-if="selected() && !selected().logo">
                            <span x-text="selected().initial" style="display:inline-flex; width:20px; height:20px; border-radius:4px; flex-shrink:0; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:var(--admin-accent); background:var(--admin-accent-muted);"></span>
                        </template>
                        <span x-show="selected()" x-text="selected() ? selected().name : ''" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        <span x-show="!selected()" style="color: var(--admin-text-muted);">{{ __('Select platform') }}</span>
                    </span>
                    <span style="color: var(--admin-text-muted);">▾</span>
                </button>
                <div
                    x-show="open"
                    x-cloak
                    @click.outside="open = false"
                    style="position:absolute; z-index:40; left:0; right:0; margin-top:6px; max-height:180px; overflow:auto; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); background:var(--admin-surface); box-shadow:var(--admin-shadow);"
                >
                    <template x-for="platform in options" :key="platform.id">
                        <button
                            type="button"
                            @click="pick(platform.id)"
                            :style="selectedId === platform.id
                                ? 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:var(--admin-accent-muted);color:var(--admin-text);border:none;text-align:left;cursor:pointer;'
                                : 'display:flex;width:100%;align-items:center;gap:10px;padding:10px 12px;background:transparent;color:var(--admin-text);border:none;text-align:left;cursor:pointer;'"
                        >
                            <template x-if="platform.logo">
                                <img :src="platform.logo" :alt="platform.name + ' logo'" width="20" height="20" style="border-radius:4px; object-fit:contain; flex-shrink:0; background:#fff;">
                            </template>
                            <template x-if="!platform.logo">
                                <span x-text="platform.initial" style="display:inline-flex; width:20px; height:20px; border-radius:4px; flex-shrink:0; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:var(--admin-accent); background:var(--admin-accent-muted);"></span>
                            </template>
                            <span x-text="platform.name"></span>
                        </button>
                    </template>
                </div>
            </div>
            <x-input-error :messages="$errors->get('platform_id')" class="mt-2" />
        </div>
    </div>
    <div class="admin-field-grid-2">
        <div class="admin-field">
            <label class="admin-label" for="company_name">{{ __('Company') }}</label>
            <input id="company_name" class="admin-input" type="text" name="company_name" value="{{ old('company_name', $application?->company_name ?? '') }}">
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>
        <div class="admin-field">
            <label class="admin-label" for="user_id">{{ __('User') }}</label>
            <select id="user_id" name="user_id" class="admin-select" required>
                <option value="">{{ __('Select user') }}</option>
                @foreach ($managedUsers as $managedUser)
                    <option value="{{ $managedUser->id }}" @selected($selectedUserId === (string) $managedUser->id)>{{ $managedUser->name }} ({{ $managedUser->email }})</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
        </div>
    </div>
    <div class="admin-field-grid-2">
        <div class="admin-field">
            <label class="admin-label" for="applied_on">{{ __('Applied on') }}</label>
            <input id="applied_on" class="admin-input" type="date" name="applied_on" value="{{ $defaultAppliedOn }}" required>
            <x-input-error :messages="$errors->get('applied_on')" class="mt-2" />
        </div>
        <div class="admin-field">
            <label class="admin-label" for="analysis_percentage">{{ __('Analysis % (optional)') }}</label>
            <input id="analysis_percentage" class="admin-input" type="number" step="0.01" min="0" max="100" name="analysis_percentage" value="{{ old('analysis_percentage', $application?->analysis_percentage ?? '') }}">
            <x-input-error :messages="$errors->get('analysis_percentage')" class="mt-2" />
        </div>
    </div>
</div>
