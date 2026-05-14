<?php

namespace App\Services\Ats\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ApyHubAtsProvider implements AtsProvider
{
    public function matchResumeToJob(string $resumeAbsolutePath, string $resumeClientFilename, string $jobDescription, string $language): array
    {
        $token = (string) config('services.apyhub.token');
        if ($token === '') {
            throw new RuntimeException(__('ApyHub token is not configured. Set APYHUB_TOKEN in your environment.'));
        }

        $submitUrl = (string) config('services.apyhub.submit_url');
        $timeout = max(10, (int) config('services.apyhub.timeout', 60));
        $pollInterval = max(0, (int) config('services.apyhub.poll_interval_seconds', 3));
        $pollMax = max($pollInterval, (int) config('services.apyhub.poll_max_seconds', 180));

        if (! is_readable($resumeAbsolutePath)) {
            throw new RuntimeException(__('Resume file is not readable.'));
        }

        $fileContents = file_get_contents($resumeAbsolutePath);
        if ($fileContents === false) {
            throw new RuntimeException(__('Could not read resume file.'));
        }

        if ($fileContents === '') {
            throw new RuntimeException(__('Resume file is empty.'));
        }

        $filename = $resumeClientFilename ?: basename($resumeAbsolutePath);
        $submitResponse = $this->sendWith429Retries(function () use ($timeout, $token, $fileContents, $filename, $submitUrl, $jobDescription, $language): Response {
            return Http::timeout($timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'apy-token' => $token,
                ])
                ->attach('file', $fileContents, $filename)
                ->post($submitUrl, [
                    'content' => $jobDescription,
                    'language' => $language !== '' ? $language : 'English',
                ]);
        }, forStatusPoll: false);

        if (! $submitResponse->successful() && $submitResponse->status() !== 202) {
            $this->throwFromResponse($submitResponse->json(), $submitResponse->status());
        }

        /** @var array<string, mixed> $submitJson */
        $submitJson = $submitResponse->json() ?: [];
        $rawStatusUrl = isset($submitJson['status_url']) && is_string($submitJson['status_url'])
            ? $submitJson['status_url']
            : null;
        $jobIdFallback = isset($submitJson['job_id']) && is_string($submitJson['job_id'])
            ? $submitJson['job_id']
            : null;
        $statusUrl = $this->resolvePollUrlAgainstSubmitBase($rawStatusUrl, $submitUrl, $jobIdFallback);
        if ($statusUrl === null || $statusUrl === '') {
            throw new RuntimeException(__('ApyHub submit response missing status_url and job_id.'));
        }

        $deadline = microtime(true) + $pollMax;

        while (microtime(true) < $deadline) {
            $statusResponse = $this->sendWith429Retries(function () use ($timeout, $token, $statusUrl): Response {
                return Http::timeout($timeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'apy-token' => $token,
                    ])
                    ->get($statusUrl);
            }, forStatusPoll: true);

            if (! $statusResponse->successful()) {
                $this->throwFromResponse($statusResponse->json(), $statusResponse->status());
            }

            /** @var array<string, mixed> $payload */
            $payload = $statusResponse->json() ?: [];

            $jobStatus = $this->readJobStatus($payload);
            if (in_array($jobStatus, ['success', 'completed', 'succeeded'], true)) {
                return $this->normalizeSuccessPayload($payload);
            }
            if (in_array($jobStatus, ['failed', 'error', 'canceled', 'cancelled'], true)) {
                throw new RuntimeException(__('ApyHub job failed with status: :status', ['status' => $jobStatus]));
            }

            if ($pollInterval > 0) {
                sleep($pollInterval);
            }
        }

        throw new RuntimeException(__('ApyHub job timed out after :seconds seconds.', ['seconds' => $pollMax]));
    }

    /**
     * ApyHub may respond with HTTP 429 when rate-limited. Retry with Retry-After or exponential backoff.
     * Status polls use fewer attempts and shorter sleeps so the overall job stays well under PHP max_execution_time.
     *
     * @param  callable(): Response  $send
     */
    private function sendWith429Retries(callable $send, bool $forStatusPoll): Response
    {
        $configured = max(1, (int) config('services.apyhub.retry_max', 5));
        $maxAttempts = $forStatusPoll ? min(3, $configured) : $configured;
        $baseMs = max(0, (int) config('services.apyhub.retry_base_ms', 500));
        $last = null;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $last = $send();
            if ($last->status() !== 429) {
                return $last;
            }
            if ($attempt >= $maxAttempts) {
                return $last;
            }
            $this->sleepAfter429($last, $attempt, $baseMs, $forStatusPoll);
        }

        if (! $last instanceof Response) {
            throw new RuntimeException(__('ApyHub request failed (no response).'));
        }

        return $last;
    }

    private function sleepAfter429(Response $response, int $attempt, int $baseMs, bool $forStatusPoll): void
    {
        $retryAfterCap = $forStatusPoll ? 10 : 30;
        $backoffCap = $forStatusPoll ? 4 : 8;

        $retryAfter = $response->header('Retry-After');
        if (is_string($retryAfter) && preg_match('/^\d+$/', trim($retryAfter))) {
            $sec = min($retryAfterCap, max(0, (int) trim($retryAfter)));
            if ($sec > 0) {
                sleep($sec);
            }

            return;
        }

        $sec = min($backoffCap, max(0, (int) ceil($baseMs / 1000 * (2 ** ($attempt - 1)))));
        if ($sec > 0) {
            sleep($sec);
        }
    }

    /**
     * SharpAPI often returns status_url on sharpapi.com as /api/v1/job/status/{id}.
     * ApyHub only routes async polling under the HR submit path, e.g.
     * .../hr/resume_job_match_score/job/status/{id}. Polling the SharpAPI-style path
     * on api.apyhub.com yields HTTP 404 ("no Route matched with those values").
     *
     * When submit_url targets SharpAPI directly, keep the API-provided status_url.
     *
     * @return non-empty-string|null
     */
    private function resolvePollUrlAgainstSubmitBase(?string $rawStatusUrl, string $submitUrl, ?string $jobIdFallback): ?string
    {
        if (is_string($rawStatusUrl) && $rawStatusUrl !== ''
            && str_contains($rawStatusUrl, 'api.apyhub.com')
            && str_contains($rawStatusUrl, '/job/status/')) {
            return $rawStatusUrl;
        }

        $jobId = null;
        if (is_string($rawStatusUrl) && $rawStatusUrl !== '' && preg_match('~/job/status/([^/?#]+)~', $rawStatusUrl, $m)) {
            $jobId = $m[1];
        } elseif (is_string($jobIdFallback) && $jobIdFallback !== '') {
            $jobId = $jobIdFallback;
        }
        if ($jobId === null || $jobId === '') {
            return null;
        }

        $usesApyHub = str_contains($submitUrl, 'api.apyhub.com');
        if ($usesApyHub) {
            $base = rtrim($submitUrl, '/');

            return $base.'/job/status/'.$jobId;
        }

        if (is_string($rawStatusUrl) && str_starts_with($rawStatusUrl, 'http')) {
            return $rawStatusUrl;
        }

        $base = rtrim($submitUrl, '/');

        return $base.'/job/status/'.$jobId;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function readJobStatus(array $payload): string
    {
        $status = data_get($payload, 'data.attributes.status');
        if (is_string($status) && $status !== '') {
            return strtolower($status);
        }
        $legacy = data_get($payload, 'status');
        if (is_string($legacy) && $legacy !== '') {
            return strtolower($legacy);
        }

        return 'pending';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeSuccessPayload(array $payload): array
    {
        $result = data_get($payload, 'data.attributes.result');
        if (! is_array($result)) {
            $result = [];
        }

        /** @var array<string, int|float> $matchScores */
        $matchScores = [];
        $rawScores = data_get($result, 'match_scores');
        if (is_array($rawScores)) {
            foreach ($rawScores as $k => $v) {
                if (! is_string($k)) {
                    continue;
                }
                if (is_int($v) || is_float($v)) {
                    $matchScores[$k] = (float) $v;
                }
            }
        }

        $overall = $matchScores['overall_match'] ?? null;
        $skills = $matchScores['skills_match'] ?? null;
        $keywords = $matchScores['technical_stack_match']
            ?? $matchScores['job_title_relevance']
            ?? $matchScores['methodologies_match']
            ?? null;

        $explanations = data_get($result, 'explanations');
        $tips = [];
        if (is_array($explanations)) {
            foreach ($explanations as $text) {
                if (is_string($text) && trim($text) !== '') {
                    $tips[] = trim($text);
                }
            }
        }

        $statusMessage = $this->statusMessageForScore($overall !== null ? (float) $overall : null);

        $skillChips = $this->metricChips($matchScores, ['overall_match']);
        $keywordChips = $this->metricChips($matchScores, ['overall_match', 'skills_match']);

        return [
            'score' => $overall !== null ? round((float) $overall, 2) : null,
            'overall_percent' => $overall !== null ? round((float) $overall, 2) : null,
            'skills_percent' => $skills !== null ? round((float) $skills, 2) : null,
            'keywords_percent' => $keywords !== null ? round((float) $keywords, 2) : null,
            'status_message' => $statusMessage,
            'tips' => array_values(array_slice($tips, 0, 20)),
            'skill_chips' => $skillChips,
            'keyword_chips' => $keywordChips,
            'raw' => $payload,
        ];
    }

    /**
     * @param  array<string, float>  $matchScores
     * @param  list<string>  $exclude
     * @return list<array{label: string, value: float|int|null}>
     */
    private function metricChips(array $matchScores, array $exclude): array
    {
        $filtered = [];
        foreach ($matchScores as $key => $value) {
            if (in_array($key, $exclude, true)) {
                continue;
            }
            $filtered[$key] = $value;
        }
        arsort($filtered, SORT_NUMERIC);
        $chips = [];
        $i = 0;
        foreach ($filtered as $key => $value) {
            if ($i++ >= 24) {
                break;
            }
            $label = Str::headline(str_replace('_', ' ', $key));
            $chips[] = ['label' => $label, 'value' => round($value, 0)];
        }

        return $chips;
    }

    private function statusMessageForScore(?float $overall): ?string
    {
        if ($overall === null) {
            return null;
        }
        if ($overall >= 80) {
            return __('Consider Applying!');
        }
        if ($overall >= 60) {
            return __('Good potential — tighten keywords and impact statements.');
        }

        return __('Needs improvement — align skills and experience with the JD.');
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function throwFromResponse(?array $json, int $status): void
    {
        $message = data_get($json, 'error.message');
        $code = data_get($json, 'error.code');
        if (is_string($message) && $message !== '') {
            if (is_int($code) || (is_string($code) && $code !== '' && preg_match('/^\d+$/', $code))) {
                throw new RuntimeException('['.$code.'] '.$message);
            }
            throw new RuntimeException($message);
        }
        if ($status === 429) {
            throw new RuntimeException(
                __('ApyHub rate limit (HTTP 429). Wait a minute before trying again, or reduce how often you run scans.')
            );
        }
        throw new RuntimeException(__('HTTP :status from ApyHub', ['status' => $status]));
    }
}
