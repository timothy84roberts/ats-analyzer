<?php

namespace Tests\Feature;

use App\Models\AtsAnalysisRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AtsAnalyzeFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private static function apyhubSuccessPayload(): array
    {
        return [
            'data' => [
                'type' => 'api_job_result',
                'id' => '5a113c4d-38e9-43e5-80f4-ec3fdea3420e',
                'attributes' => [
                    'status' => 'success',
                    'type' => 'hr_resume_job_match_score',
                    'result' => [
                        'match_scores' => [
                            'overall_match' => 88,
                            'skills_match' => 92,
                            'technical_stack_match' => 90,
                            'experience_match' => 85,
                        ],
                        'explanations' => [
                            'skills_match' => 'Candidate lists React and Node.js.',
                            'experience_match' => 'Five years in similar roles.',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_analyze_flashes_results_and_does_not_persist_run(): void
    {
        Config::set('ats.enabled', true);
        Config::set('services.apyhub.token', 'test-token');
        Config::set('services.apyhub.poll_interval_seconds', 0);

        $success = self::apyhubSuccessPayload();
        $statusUrl = 'https://api.apyhub.com/sharpapi/api/v1/hr/resume_job_match_score/job/status/abc';
        $pollCount = 0;

        Http::fake(function ($request) use ($statusUrl, &$pollCount, $success) {
            $url = $request->url();
            if (str_contains($url, '/hr/resume_job_match_score') && ! str_contains($url, '/job/status/') && $request->method() === 'POST') {
                return Http::response([
                    'status_url' => $statusUrl,
                    'job_id' => 'abc',
                ], 202);
            }
            if (str_contains($url, '/job/status/')) {
                $pollCount++;
                if ($pollCount === 1) {
                    return Http::response(['data' => ['attributes' => ['status' => 'pending']]], 200);
                }

                return Http::response($success, 200);
            }

            return Http::response(['error' => ['message' => 'unexpected URL '.$url]], 500);
        });

        $user = User::factory()->create();
        $pdfStub = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".str_repeat('0', 200);

        $this->assertSame(0, AtsAnalysisRun::query()->count());

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('ats-scanner.analyze'), [
                'content' => str_repeat('a', 25).' job description text here',
                'language' => 'English',
                'resume' => UploadedFile::fake()->createWithContent('resume.pdf', $pdfStub),
            ])
            ->assertOk()
            ->assertSee('88', false)
            ->assertSee('MATCH SCORE', false);

        $this->assertSame(0, AtsAnalysisRun::query()->count());
    }

    public function test_analyze_rewrites_sharpapi_style_status_url_when_using_apyhub_submit(): void
    {
        Config::set('ats.enabled', true);
        Config::set('services.apyhub.token', 'test-token');
        Config::set('services.apyhub.poll_interval_seconds', 0);

        $success = self::apyhubSuccessPayload();
        $jobUuid = '45da1abe-35a3-4628-ae70-e2cb48c084c2';
        $expectedPollUrl = 'https://api.apyhub.com/sharpapi/api/v1/hr/resume_job_match_score/job/status/'.$jobUuid;
        $pollCount = 0;

        Http::fake(function ($request) use ($jobUuid, $expectedPollUrl, &$pollCount, $success) {
            $url = $request->url();
            if (str_contains($url, '/hr/resume_job_match_score') && ! str_contains($url, '/job/status/') && $request->method() === 'POST') {
                return Http::response([
                    'status_url' => 'https://sharpapi.com/api/v1/job/status/'.$jobUuid,
                    'job_id' => $jobUuid,
                ], 202);
            }
            if ($url === $expectedPollUrl) {
                $pollCount++;
                if ($pollCount === 1) {
                    return Http::response(['data' => ['attributes' => ['status' => 'pending']]], 200);
                }

                return Http::response($success, 200);
            }

            return Http::response(['error' => ['message' => 'unexpected URL '.$url]], 500);
        });

        $user = User::factory()->create();
        $pdfStub = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".str_repeat('0', 200);

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('ats-scanner.analyze'), [
                'content' => str_repeat('c', 25).' job description text here',
                'language' => 'English',
                'resume' => UploadedFile::fake()->createWithContent('resume.pdf', $pdfStub),
            ])
            ->assertOk()
            ->assertSee('88', false);

        $this->assertGreaterThanOrEqual(1, $pollCount);
    }

    public function test_analyze_returns_error_without_persisting_run_when_submit_not_accepted(): void
    {
        Config::set('ats.enabled', true);
        Config::set('services.apyhub.token', 'test-token');
        Config::set('services.apyhub.poll_interval_seconds', 0);

        Http::fake([
            'https://api.apyhub.com/sharpapi/api/v1/hr/resume_job_match_score' => Http::response([
                'error' => ['message' => 'Invalid token'],
            ], 401),
        ]);

        $user = User::factory()->create();
        $pdfStub = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".str_repeat('0', 200);
        $file = UploadedFile::fake()->createWithContent('resume.pdf', $pdfStub);

        $response = $this->actingAs($user)->post(route('ats-scanner.analyze'), [
            'content' => str_repeat('b', 30).' more jd text for validation',
            'language' => 'English',
            'resume' => $file,
        ]);

        $response->assertSessionHasErrors('ats');
        $this->assertSame(0, AtsAnalysisRun::query()->count());
    }

    public function test_analyze_json_returns_panel_html_and_ok_flag_on_success(): void
    {
        Config::set('ats.enabled', true);
        Config::set('services.apyhub.token', 'test-token');
        Config::set('services.apyhub.poll_interval_seconds', 0);

        $success = self::apyhubSuccessPayload();
        $statusUrl = 'https://api.apyhub.com/sharpapi/api/v1/hr/resume_job_match_score/job/status/abc';
        $pollCount = 0;

        Http::fake(function ($request) use ($statusUrl, &$pollCount, $success) {
            $url = $request->url();
            if (str_contains($url, '/hr/resume_job_match_score') && ! str_contains($url, '/job/status/') && $request->method() === 'POST') {
                return Http::response([
                    'status_url' => $statusUrl,
                    'job_id' => 'abc',
                ], 202);
            }
            if (str_contains($url, '/job/status/')) {
                $pollCount++;
                if ($pollCount === 1) {
                    return Http::response(['data' => ['attributes' => ['status' => 'pending']]], 200);
                }

                return Http::response($success, 200);
            }

            return Http::response(['error' => ['message' => 'unexpected URL '.$url]], 500);
        });

        $user = User::factory()->create();
        $pdfStub = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".str_repeat('0', 200);

        $response = $this->actingAs($user)->postJson(route('ats-scanner.analyze'), [
            'content' => str_repeat('d', 25).' job description text here',
            'language' => 'English',
            'resume' => UploadedFile::fake()->createWithContent('resume.pdf', $pdfStub),
        ]);

        $response->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonStructure(['ok', 'status', 'panel_html']);
        $this->assertStringContainsString('88', (string) $response->json('panel_html'));
        $this->assertSame(0, AtsAnalysisRun::query()->count());
    }

    public function test_analyze_json_returns_error_panel_when_provider_fails(): void
    {
        Config::set('ats.enabled', true);
        Config::set('services.apyhub.token', 'test-token');
        Config::set('services.apyhub.poll_interval_seconds', 0);

        Http::fake([
            'https://api.apyhub.com/sharpapi/api/v1/hr/resume_job_match_score' => Http::response([
                'error' => ['message' => 'Invalid token'],
            ], 401),
        ]);

        $user = User::factory()->create();
        $pdfStub = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".str_repeat('0', 200);

        $response = $this->actingAs($user)->postJson(route('ats-scanner.analyze'), [
            'content' => str_repeat('e', 30).' more jd text for validation',
            'language' => 'English',
            'resume' => UploadedFile::fake()->createWithContent('resume.pdf', $pdfStub),
        ]);

        $response->assertOk()
            ->assertJson(['ok' => false]);
        $this->assertStringContainsString(__('Analysis failed'), (string) $response->json('panel_html'));
        $this->assertStringContainsString('Invalid token', (string) $response->json('panel_html'));
    }

    public function test_analyze_retries_submit_on_429_then_completes(): void
    {
        Config::set('ats.enabled', true);
        Config::set('services.apyhub.token', 'test-token');
        Config::set('services.apyhub.poll_interval_seconds', 0);
        Config::set('services.apyhub.retry_base_ms', 0);
        Config::set('services.apyhub.retry_max', 5);

        $success = self::apyhubSuccessPayload();
        $statusUrl = 'https://api.apyhub.com/sharpapi/api/v1/hr/resume_job_match_score/job/status/abc';
        $pollCount = 0;
        $submitHits = 0;

        Http::fake(function ($request) use ($statusUrl, &$pollCount, &$submitHits, $success) {
            $url = $request->url();
            if (str_contains($url, '/hr/resume_job_match_score') && ! str_contains($url, '/job/status/') && $request->method() === 'POST') {
                $submitHits++;
                if ($submitHits < 3) {
                    return Http::response(['error' => ['message' => 'Too Many Requests']], 429);
                }

                return Http::response([
                    'status_url' => $statusUrl,
                    'job_id' => 'abc',
                ], 202);
            }
            if (str_contains($url, '/job/status/')) {
                $pollCount++;
                if ($pollCount === 1) {
                    return Http::response(['data' => ['attributes' => ['status' => 'pending']]], 200);
                }

                return Http::response($success, 200);
            }

            return Http::response(['error' => ['message' => 'unexpected URL '.$url]], 500);
        });

        $user = User::factory()->create();
        $pdfStub = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".str_repeat('0', 200);

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('ats-scanner.analyze'), [
                'content' => str_repeat('f', 25).' job description text here',
                'language' => 'English',
                'resume' => UploadedFile::fake()->createWithContent('resume.pdf', $pdfStub),
            ])
            ->assertOk()
            ->assertSee('88', false);

        $this->assertSame(3, $submitHits);
    }
}
