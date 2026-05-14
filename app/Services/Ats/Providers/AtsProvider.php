<?php

namespace App\Services\Ats\Providers;

/**
 * Pluggable ATS scoring backend (e.g. ApyHub SharpAPI).
 *
 * @phpstan-type NormalizedAtsResult array{
 *   score: float|null,
 *   overall_percent: float|null,
 *   skills_percent: float|null,
 *   keywords_percent: float|null,
 *   status_message: string|null,
 *   tips: list<string>,
 *   skill_chips: list<array{label: string, value: float|int|null}>,
 *   keyword_chips: list<array{label: string, value: float|int|null}>,
 *   raw: array<string, mixed>
 * }
 */
interface AtsProvider
{
    /**
     * @return NormalizedAtsResult
     */
    public function matchResumeToJob(string $resumeAbsolutePath, string $resumeClientFilename, string $jobDescription, string $language): array;
}
