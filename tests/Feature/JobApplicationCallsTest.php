<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\JobApplicationCall;
use App\Models\PipelineStage;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationCallsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_book_calls_on_application(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'Frontend role',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Globex',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $application = JobApplication::query()->where('title', 'Frontend role')->first();
        $this->assertNotNull($application);

        $when = now()->addDays(3)->seconds(0);

        $this->actingAs($user)->post(route('applications.calls.store', $application), [
            'title' => 'Recruiter phone screen',
            'description' => "Zoom link in invite.\nPrepare STAR stories.",
            'scheduled_at' => $when->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $this->actingAs($user)->post(route('applications.calls.store', $application), [
            'title' => 'Technical interview',
            'description' => null,
            'scheduled_at' => $when->copy()->addDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.index'));

        $this->assertDatabaseCount('job_application_calls', 2);
        $this->assertDatabaseHas('job_application_calls', [
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'title' => 'Recruiter phone screen',
            'description' => "Zoom link in invite.\nPrepare STAR stories.",
        ]);
        $this->assertDatabaseHas('job_application_calls', [
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'title' => 'Technical interview',
            'description' => null,
        ]);

        $stored = JobApplicationCall::query()->where('title', 'Technical interview')->first();
        $this->assertNotNull($stored);
        $this->assertSame(
            $when->copy()->addDay()->format('Y-m-d H:i'),
            $stored->scheduled_at->format('Y-m-d H:i')
        );
    }

    public function test_user_can_delete_call_from_application_show(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'DevOps role',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Initech',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $application = JobApplication::query()->where('title', 'DevOps role')->first();
        $this->assertNotNull($application);

        $call = JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'title' => 'HM chat',
            'description' => null,
            'scheduled_at' => now()->addWeek(),
        ]);

        $this->actingAs($user)
            ->from(route('applications.show', $application))
            ->delete(route('applications.calls.destroy', [$application, $call]))
            ->assertRedirect(route('applications.show', $application))
            ->assertSessionHas('status', __('Call deleted.'));

        $this->assertDatabaseMissing('job_application_calls', ['id' => $call->id]);
    }

    public function test_delete_call_returns_404_when_call_belongs_to_another_application(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'Call app A',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'A',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();
        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'Call app B',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'B',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $appA = JobApplication::query()->where('title', 'Call app A')->first();
        $appB = JobApplication::query()->where('title', 'Call app B')->first();
        $this->assertNotNull($appA);
        $this->assertNotNull($appB);

        $call = JobApplicationCall::create([
            'job_application_id' => $appA->id,
            'user_id' => $user->id,
            'title' => 'On A',
            'description' => null,
            'scheduled_at' => now()->addDay(),
        ]);

        $this->actingAs($user)
            ->delete(route('applications.calls.destroy', [$appB, $call]))
            ->assertNotFound();

        $this->assertDatabaseHas('job_application_calls', ['id' => $call->id]);
    }
}
