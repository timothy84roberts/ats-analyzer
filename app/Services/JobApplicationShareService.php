<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class JobApplicationShareService
{
    /**
     * Shared Drive folder "Job share":
     * https://drive.google.com/drive/folders/1jnLo3dJbuEPVkLId2ys2UBHXkcYG9Bhy
     */
    public const DEFAULT_FOLDER_ID = '1jnLo3dJbuEPVkLId2ys2UBHXkcYG9Bhy';

    public const DEFAULT_FOLDER_NAME = 'Job share';

    public function share(JobApplication $application): bool
    {
        if (! config('services.job_application_share.enabled')) {
            return false;
        }

        $webhookUrl = trim((string) config('services.job_application_share.webhook_url'));
        $token = trim((string) config('services.job_application_share.token'));

        if ($webhookUrl === '' || $token === '') {
            Log::warning('Job application share skipped: webhook URL or token is not configured.');

            return false;
        }

        $folderId = trim((string) config('services.job_application_share.folder_id')) ?: self::DEFAULT_FOLDER_ID;

        $application->loadMissing(['user', 'country', 'platform']);

        $payload = [
            'token' => $token,
            'folder_id' => $folderId,
            'application_id' => $application->id,
            'title' => $application->title,
            'folder_name' => $this->shareFolderName($application),
            'description_html' => RichTextSanitizer::sanitize($application->description),
            'description_text' => $this->plainDescription($application->description),
            'company_name' => $application->company_name,
            'user_name' => $application->user?->name,
            'applied_on' => $application->applied_on?->format('Y-m-d'),
            'country' => $application->country?->name,
            'platform' => $application->platform?->name,
            'resume' => null,
        ];

        if ($application->hasResume() && Storage::disk('local')->exists($application->resume_path)) {
            $binary = Storage::disk('local')->get($application->resume_path);
            $payload['resume'] = [
                'filename' => $this->resumeFilename($application),
                'mime_type' => 'application/pdf',
                'content_base64' => base64_encode($binary),
            ];
        }

        try {
            $body = $this->postToAppsScript($webhookUrl, $payload);
            $ok = is_array($body) && (($body['ok'] ?? false) === true);

            if (! $ok) {
                Log::warning('Job application share webhook failed.', [
                    'application_id' => $application->id,
                    'folder_id' => $folderId,
                    'body' => $body,
                ]);

                return false;
            }

            Log::info('Job application shared to Drive.', [
                'application_id' => $application->id,
                'folder_id' => $folderId,
                'folder_url' => $body['folder_url'] ?? null,
                'resume_url' => $body['resume']['url'] ?? null,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::warning('Job application share webhook error.', [
                'application_id' => $application->id,
                'folder_id' => $folderId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * GET the web app. A live deployment returns JSON with ok:true.
     * 401/404/HTML means redeploy with "Anyone" access.
     *
     * @return array{ok: bool, status: int, body: mixed, message: string}
     */
    public function probeWebhook(?string $webhookUrl = null): array
    {
        $webhookUrl = trim((string) ($webhookUrl ?? config('services.job_application_share.webhook_url')));

        if ($webhookUrl === '') {
            return [
                'ok' => false,
                'status' => 0,
                'body' => null,
                'message' => 'JOB_APPLICATION_SHARE_WEBHOOK_URL is empty.',
            ];
        }

        try {
            $response = $this->requestAppsScript('GET', $webhookUrl);
            $status = $response->status();
            $json = $response->json();
            $isJsonOk = is_array($json) && (($json['ok'] ?? false) === true);

            if ($status === 404) {
                return [
                    'ok' => false,
                    'status' => $status,
                    'body' => $json,
                    'message' => 'Webhook URL returned HTTP 404. The Apps Script deployment is missing or the /exec URL is wrong. Deploy → New deployment (or Manage deployments → New version) and paste the new /exec URL into .env.',
                ];
            }

            if ($status === 401 || $status === 403) {
                return [
                    'ok' => false,
                    'status' => $status,
                    'body' => $json,
                    'message' => 'Webhook returned HTTP '.$status.'. Redeploy the web app with Who has access: Anyone, then authorize Drive.',
                ];
            }

            if (! $response->successful() || ! $isJsonOk) {
                return [
                    'ok' => false,
                    'status' => $status,
                    'body' => $json ?? mb_substr($response->body(), 0, 300),
                    'message' => 'Webhook is not healthy (HTTP '.$status.'). Open the /exec URL in a browser — you should see JSON, not a Google login page.',
                ];
            }

            $folderId = is_array($json) ? (string) ($json['folder_id'] ?? '') : '';
            $scriptVersion = is_array($json) ? (string) ($json['script_version'] ?? '') : '';
            $expected = self::DEFAULT_FOLDER_ID;

            $hints = [];
            $hints[] = $folderId === $expected
                ? 'Folder ID matches Job share.'
                : 'Warning: script folder_id is "'.$folderId.'"; expected Job share id '.$expected.'.';

            if ($scriptVersion === 'naming-v3') {
                $hints[] = 'script_version=naming-v3 (folder = "{company} - {title}", description.txt only).';
            } elseif ($scriptVersion === '') {
                $hints[] = 'WARNING: script_version missing — you are still on the OLD Apps Script. Paste ShareJobApplication.gs and Deploy → Manage deployments → New version.';
            } else {
                $hints[] = 'script_version='.$scriptVersion.' (expected naming-v3). Redeploy the latest ShareJobApplication.gs.';
            }

            return [
                'ok' => true,
                'status' => $status,
                'body' => $json,
                'message' => 'Webhook is live. '.implode(' ', $hints),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'body' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Apps Script /exec returns 302 → googleusercontent.com.
     * Following with GET causes HTTP 405. Always keep POST across redirects.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function postToAppsScript(string $webhookUrl, array $payload): ?array
    {
        $response = $this->requestAppsScript('POST', $webhookUrl, $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Apps Script HTTP '.$response->status().': '.mb_substr($response->body(), 0, 300)
            );
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function requestAppsScript(string $method, string $url, ?array $payload = null): Response
    {
        $timeout = max(10, (int) config('services.job_application_share.timeout', 60));
        $maxHops = 5;
        $currentUrl = $url;
        $response = null;

        for ($hop = 0; $hop < $maxHops; $hop++) {
            $pending = Http::timeout($timeout)
                ->withOptions(['allow_redirects' => false])
                ->acceptJson();

            $response = strtoupper($method) === 'GET'
                ? $pending->get($currentUrl)
                : $pending->asJson()->post($currentUrl, $payload ?? []);

            if (! in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                return $response;
            }

            $location = $response->header('Location');
            if (! is_string($location) || $location === '') {
                throw new RuntimeException('Apps Script redirect missing Location header (HTTP '.$response->status().').');
            }

            // Absolute or relative Location
            if (str_starts_with($location, '/')) {
                $parts = parse_url($currentUrl);
                $location = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? 'script.google.com').$location;
            }

            $currentUrl = $location;

            // 303 historically means switch to GET — Apps Script still needs the original method for our webhook.
            if ($response->status() === 303 && strtoupper($method) === 'POST') {
                // keep POST
            }
        }

        throw new RuntimeException('Apps Script exceeded redirect limit.');
    }

    private function plainDescription(?string $html): string
    {
        return RichTextSanitizer::toPlainText($html);
    }

    /**
     * Drive subfolder: "{company name} - {job title}", e.g. "BRG - Software Engineer".
     */
    private function shareFolderName(JobApplication $application): string
    {
        $title = trim((string) $application->title) ?: 'Untitled';
        $company = trim((string) $application->company_name);

        $name = $company === '' ? $title : $company.' - '.$title;

        $safe = trim((string) preg_replace('/[\\\\\/:*?"<>|]+/', '-', $name));
        $safe = trim(preg_replace('/\s+/', ' ', $safe) ?? $safe);

        return mb_substr($safe !== '' ? $safe : 'Untitled', 0, 120);
    }

    private function resumeFilename(JobApplication $application): string
    {
        $company = trim((string) $application->company_name);
        $safeCompany = $company === ''
            ? ''
            : trim((string) preg_replace('/[\\\\\/:*?"<>|]+/', '-', $company));

        return $safeCompany === ''
            ? 'Shayne Guiliano.pdf'
            : 'Shayne Guiliano_'.$safeCompany.'.pdf';
    }
}
