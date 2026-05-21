<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--admin-accent-muted);color:var(--admin-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            </div>
            <h1 style="margin:0;">{{ __('ATS Scan') }}</h1>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="admin-alert admin-alert--success" style="margin-bottom: 16px;">{{ session('status') }}</div>
    @endif
    @if (session('warning'))
        <div class="admin-alert admin-alert--warn" style="margin-bottom: 16px;">{{ session('warning') }}</div>
    @endif
    @if ($errors->has('ats'))
        <div class="admin-alert admin-alert--error" style="margin-bottom: 16px;">{{ $errors->first('ats') }}</div>
    @endif

    <div id="ats-scan-ajax-flash-root"></div>

    <div class="admin-alert admin-alert--warn" style="margin-bottom: 20px;">
        {{ __('Resume files are not stored on this server — they are only sent to the scoring API for this request. Configure APYHUB_TOKEN for ApyHub / SharpAPI. Match results are shown once on this page and are not saved.') }}
    </div>

    <div
        id="ats-scan-loading"
        class="ats-scan-loading"
        hidden
        role="status"
        aria-live="polite"
        aria-hidden="true"
        aria-busy="false"
    >
        <div class="ats-scan-loading__box">
            <img
                class="ats-scan-loading__logo"
                src="{{ asset('assets/logo-mark.svg') }}"
                alt=""
                width="56"
                height="56"
                decoding="async"
            >
            <p class="ats-scan-loading__text">{{ __('Analyzing resume…') }}</p>
            <p class="ats-scan-loading__hint">{{ __('This may take up to a few minutes.') }}</p>
        </div>
    </div>

    <div class="admin-grid-2 ats-scan-grid">
        <div class="admin-card admin-card--pad" id="ats-scan-form">
            <form id="ats-analyze-form" class="admin-form-stack" method="post" action="{{ route('ats-scanner.analyze') }}" enctype="multipart/form-data">
                @csrf

                <div class="admin-field">
                    <label class="admin-label" for="content">{{ __('Job description') }}</label>
                    <p class="admin-muted-hint" style="margin: 0 0 8px;">
                        {{ __('Paste or compose the full posting. Formatting, lists, and links are supported. Minimum 20 characters of text.') }}
                    </p>
                    <div
                        class="admin-summernote-wrap ats-scan-editor-wrap"
                        id="content-editor-wrap"
                        data-placeholder="{{ __('Formatting, lists, and links are supported.') }}"
                    >
                        <textarea id="content" name="content" rows="10" class="w-full">{!! \App\Support\RichTextSanitizer::sanitize(old('content')) !!}</textarea>
                    </div>
                    <x-input-error :messages="$errors->get('content')" class="mt-2" />
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
                            var ta = document.getElementById('content');
                            var wrap = document.getElementById('content-editor-wrap');
                            if (ta && typeof jQuery !== 'undefined' && jQuery.fn.summernote) {
                                var $ = jQuery;
                                var ph = wrap && wrap.getAttribute('data-placeholder') ? wrap.getAttribute('data-placeholder') : '';
                                $(ta).summernote({
                                    placeholder: ph,
                                    height: 280,
                                    disableDragAndDrop: true,
                                    toolbar: [
                                        ['style', ['style']],
                                        ['font', ['bold', 'italic', 'underline', 'clear']],
                                        ['para', ['ul', 'ol', 'paragraph']],
                                        ['insert', ['link']],
                                        ['view', ['fullscreen', 'codeview']],
                                    ],
                                });
                            }
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
                            var analyzeForm = document.getElementById('ats-analyze-form');
                            var loadingEl = document.getElementById('ats-scan-loading');
                            var panelSlot = document.getElementById('ats-scan-panel-slot');
                            var flashRoot = document.getElementById('ats-scan-ajax-flash-root');

                            function showAtsScanLoading() {
                                if (!loadingEl) {
                                    return;
                                }
                                loadingEl.hidden = false;
                                loadingEl.setAttribute('aria-hidden', 'false');
                                loadingEl.setAttribute('aria-busy', 'true');
                                document.body.classList.add('ats-scan-loading--active');
                            }

                            function hideAtsScanLoading() {
                                if (!loadingEl) {
                                    return;
                                }
                                loadingEl.hidden = true;
                                loadingEl.setAttribute('aria-hidden', 'true');
                                loadingEl.setAttribute('aria-busy', 'false');
                                document.body.classList.remove('ats-scan-loading--active');
                            }

                            function clearAjaxFlashes() {
                                if (flashRoot) {
                                    flashRoot.innerHTML = '';
                                }
                            }

                            function appendAjaxAlert(className, text) {
                                if (!flashRoot || !text) {
                                    return;
                                }
                                var div = document.createElement('div');
                                div.className = className;
                                div.style.marginBottom = '16px';
                                div.textContent = text;
                                flashRoot.appendChild(div);
                            }

                            if (analyzeForm && loadingEl && panelSlot) {
                                analyzeForm.addEventListener('submit', function (e) {
                                    e.preventDefault();
                                    if (!analyzeForm.reportValidity()) {
                                        return;
                                    }

                                    var taContent = document.getElementById('content');
                                    if (taContent && typeof jQuery !== 'undefined' && jQuery.fn.summernote) {
                                        var $ta = jQuery(taContent);
                                        if ($ta.data('summernote') || $ta.next('.note-editor').length) {
                                            taContent.value = $ta.summernote('code');
                                        }
                                    }

                                    var submitBtn = analyzeForm.querySelector('[data-analyze-submit]');
                                    if (submitBtn) {
                                        submitBtn.disabled = true;
                                    }

                                    showAtsScanLoading();

                                    var fd = new FormData(analyzeForm);
                                    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                                    var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

                                    fetch(analyzeForm.action, {
                                        method: 'POST',
                                        body: fd,
                                        headers: {
                                            Accept: 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': csrf,
                                        },
                                        credentials: 'same-origin',
                                    })
                                        .then(function (res) {
                                            var ct = res.headers.get('Content-Type') || '';
                                            if (ct.indexOf('application/json') !== -1) {
                                                return res.json().then(function (data) {
                                                    return { res: res, data: data };
                                                });
                                            }
                                            return res.text().then(function (text) {
                                                return {
                                                    res: res,
                                                    data: { _raw: text },
                                                };
                                            });
                                        })
                                        .then(function (payload) {
                                            var res = payload.res;
                                            var data = payload.data || {};
                                            clearAjaxFlashes();

                                            if (res.status === 422) {
                                                var msg = data.message || @json(__('Please fix the errors below.'));
                                                appendAjaxAlert('admin-alert admin-alert--error', msg);
                                                var lines = [];
                                                if (data.errors && typeof data.errors === 'object') {
                                                    Object.keys(data.errors).forEach(function (key) {
                                                        var arr = data.errors[key];
                                                        if (Array.isArray(arr)) {
                                                            arr.forEach(function (line) {
                                                                lines.push(line);
                                                            });
                                                        }
                                                    });
                                                }
                                                if (lines.length) {
                                                    appendAjaxAlert('admin-alert admin-alert--warn', lines.join(' '));
                                                }
                                                return;
                                            }

                                            var panelHtml = typeof data.panel_html === 'string' ? data.panel_html : '';
                                            if (panelHtml.length > 0) {
                                                panelSlot.innerHTML = panelHtml;
                                            }

                                            if (!res.ok) {
                                                if (!panelHtml.length) {
                                                    var errText = typeof data.message === 'string' && data.message.length
                                                        ? data.message
                                                        : @json(__('Request failed. Please try again.'));
                                                    appendAjaxAlert('admin-alert admin-alert--error', errText);
                                                }
                                                return;
                                            }

                                            if (data.ok && data.status) {
                                                appendAjaxAlert('admin-alert admin-alert--success', data.status);
                                            }
                                        })
                                        .catch(function () {
                                            clearAjaxFlashes();
                                            appendAjaxAlert(
                                                'admin-alert admin-alert--error',
                                                @json(__('Network error. Check your connection and try again.'))
                                            );
                                        })
                                        .finally(function () {
                                            hideAtsScanLoading();
                                            if (submitBtn) {
                                                submitBtn.disabled = false;
                                            }
                                        });
                                });
                            }
                        });
                    </script>
                @endPushOnce

                <div class="admin-field" style="margin-top: 8px;">
                    <label class="admin-label" for="resume">{{ __('Resume') }}</label>
                    <p class="admin-muted-hint" style="margin: 0 0 8px;">{{ __('PDF, DOCX, TXT, or RTF — max 10 MB.') }}</p>
                    <div class="admin-file-upload">
                        <label class="admin-file-upload__btn-wrap">
                            <span class="admin-btn admin-btn--primary">{{ __('Choose file') }}</span>
                            <input
                                id="resume"
                                type="file"
                                name="resume"
                                class="admin-file-upload__input"
                                accept=".pdf,.doc,.docx,.txt,.rtf,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain,application/rtf"
                                required
                            >
                        </label>
                        <span
                            id="resume-file-name"
                            class="admin-file-upload__filename"
                            data-empty="{{ __('No file chosen') }}"
                        >{{ __('No file chosen') }}</span>
                    </div>
                    <x-input-error :messages="$errors->get('resume')" class="mt-2" />
                </div>

                <div class="ats-scan-inline">
                    <div class="admin-form-stack" style="gap: 6px; flex: 1;">
                        <label class="admin-label" for="language">{{ __('Explanation language') }}</label>
                        <input id="language" class="admin-input" type="text" name="language" value="{{ old('language', 'English') }}" maxlength="64">
                        <x-input-error :messages="$errors->get('language')" class="mt-2" />
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn--primary" data-analyze-submit>{{ __('Analyze match') }}</button>
                </div>
            </form>
        </div>

        <div id="ats-scan-panel-slot" class="ats-scan-panel-slot">
            @include('ats.partials.scan-panel', [
                'normalized' => $normalized,
                'atsError' => $errors->has('ats') ? $errors->first('ats') : null,
            ])
        </div>
    </div>
</x-app-layout>
