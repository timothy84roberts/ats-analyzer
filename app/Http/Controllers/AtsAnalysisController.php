<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeAtsRequest;
use App\Services\AtsAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AtsAnalysisController extends Controller
{
    private const JSON_ENCODE = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;

    public function index(Request $request): View
    {
        $normalized = $request->session()->get('ats_normalized');

        return view('ats.index', [
            'normalized' => is_array($normalized) ? $normalized : null,
        ]);
    }

    public function analyze(AnalyzeAtsRequest $request, AtsAnalysisService $analysisService): JsonResponse|RedirectResponse
    {
        $this->relaxPhpTimeLimitForAtsAnalysis();

        try {
            $validated = $request->validated();

            $result = $analysisService->analyze(
                $request->file('resume'),
                $validated['content'],
                $validated['language'],
            );

            if ($request->expectsJson()) {
                if (! $result['ok']) {
                    return response()->json([
                        'ok' => false,
                        'message' => $result['message'],
                        'panel_html' => $this->renderScanPanelOrFallback(null, $result['message']),
                    ], 200, [], self::JSON_ENCODE);
                }

                return response()->json([
                    'ok' => true,
                    'status' => __('Analysis completed.'),
                    'panel_html' => $this->renderScanPanelOrFallback($result['normalized'], null),
                ], 200, [], self::JSON_ENCODE);
            }

            $redirect = redirect()
                ->route('ats-scanner.index')
                ->withInput($request->except('resume'));

            if (! $result['ok']) {
                return $redirect->withErrors(['ats' => $result['message']]);
            }

            return $redirect
                ->with('ats_normalized', $result['normalized'])
                ->with('status', __('Analysis completed.'));
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                $message = config('app.debug')
                    ? $e->getMessage()
                    : __('Something went wrong while analyzing. Please try again.');

                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'panel_html' => $this->renderScanPanelOrFallback(null, $message),
                ], 500, [], self::JSON_ENCODE);
            }

            throw $e;
        }
    }

    /**
     * ApyHub polling can run up to poll_max_seconds; PHP max_execution_time is often 120s — raise the limit for this request.
     */
    private function relaxPhpTimeLimitForAtsAnalysis(): void
    {
        if (! function_exists('set_time_limit')) {
            return;
        }

        $pollMax = max(30, (int) config('services.apyhub.poll_max_seconds', 180));
        $httpTimeout = max(10, (int) config('services.apyhub.timeout', 60));
        $buffer = 180;

        $seconds = $pollMax + $httpTimeout + $buffer;
        @set_time_limit($seconds);
        if (function_exists('ini_set')) {
            @ini_set('max_execution_time', (string) $seconds);
        }
    }

    /**
     * @param  array<string, mixed>|null  $normalized
     */
    private function renderScanPanelOrFallback(?array $normalized, ?string $atsError): string
    {
        try {
            return view('ats.partials.scan-panel', [
                'normalized' => $normalized,
                'atsError' => $atsError,
            ])->render();
        } catch (Throwable $e) {
            report($e);
            $msg = e($atsError ?? __('Something went wrong while analyzing. Please try again.'));

            return '<aside class="ats-scan-panel" aria-label="'.e(__('Match results')).'">'
                .'<div class="ats-scan-panel__inner ats-scan-panel__empty">'
                .'<p class="ats-scan-empty-title">'.e(__('Analysis failed')).'</p>'
                .'<p class="ats-scan-empty-desc">'.$msg.'</p>'
                .'<a href="#ats-scan-form" class="ats-scan-cta">'.e(__('Try again')).'</a>'
                .'</div></aside>';
        }
    }
}
