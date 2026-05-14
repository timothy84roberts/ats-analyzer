<?php

namespace App\Services;

use App\Services\Ats\Providers\AtsProvider;
use Illuminate\Http\UploadedFile;
use Throwable;

class AtsAnalysisService
{
    public function __construct(
        private readonly AtsProvider $atsProvider
    ) {}

    /**
     * Call provider with the uploaded file in memory only (no persistent resume copy).
     * Results are not stored; return them to the caller for one-time display (e.g. session flash).
     *
     * @return array{ok: true, normalized: array<string, mixed>}|array{ok: false, message: string}
     */
    public function analyze(
        UploadedFile $resume,
        string $jobDescription,
        string $language
    ): array {
        $absolutePath = $resume->getRealPath();
        if (! is_string($absolutePath) || $absolutePath === '' || ! is_readable($absolutePath)) {
            return [
                'ok' => false,
                'message' => (string) __('Could not read the uploaded resume (temporary file missing).'),
            ];
        }

        try {
            $normalized = $this->atsProvider->matchResumeToJob(
                $absolutePath,
                $resume->getClientOriginalName(),
                $jobDescription,
                $language
            );

            return [
                'ok' => true,
                'normalized' => [
                    'score' => $normalized['score'],
                    'overall_percent' => $normalized['overall_percent'],
                    'skills_percent' => $normalized['skills_percent'],
                    'keywords_percent' => $normalized['keywords_percent'],
                    'status_message' => $normalized['status_message'],
                    'tips' => $normalized['tips'],
                    'skill_chips' => $normalized['skill_chips'],
                    'keyword_chips' => $normalized['keyword_chips'],
                ],
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
