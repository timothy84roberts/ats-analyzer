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

class UpcomingCallBannerTest extends TestCase
{
    use RefreshDatabase;

    private function seedUserWithApplication(User $user): JobApplication
    {
        $country = Country::factory()->create();
        $platform = Platform::factory()->create();
        PipelineStage::factory()->create(['slug' => 'resume_submitted', 'sort_order' => 10]);

        $this->actingAs($user)->post(route('applications.store'), [
            'title' => 'Banner test job',
            'description' => null,
            'country_id' => $country->id,
            'company_name' => 'Co',
            'platform_id' => $platform->id,
            'analysis_percentage' => null,
            'applied_on' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $application = JobApplication::query()->where('title', 'Banner test job')->first();
        $this->assertNotNull($application);

        return $application;
    }

    public function test_banner_shows_on_dashboard_when_call_within_twelve_hours(): void
    {
        $user = User::factory()->create();
        $application = $this->seedUserWithApplication($user);

        JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'title' => 'Soon',
            'description' => null,
            'scheduled_at' => now()->addHours(6),
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Upcoming call reminder'), false);
    }

    public function test_banner_hidden_when_next_call_is_beyond_twelve_hours(): void
    {
        $user = User::factory()->create();
        $application = $this->seedUserWithApplication($user);

        JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'title' => 'Later',
            'description' => null,
            'scheduled_at' => now()->addHours(13),
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(__('Upcoming call reminder'), false);
    }

    public function test_banner_hidden_for_past_scheduled_call(): void
    {
        $user = User::factory()->create();
        $application = $this->seedUserWithApplication($user);

        JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'title' => 'Past',
            'description' => null,
            'scheduled_at' => now()->subHour(),
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(__('Upcoming call reminder'), false);
    }

    public function test_banner_link_points_to_application_show_with_scheduled_calls_fragment(): void
    {
        $user = User::factory()->create();
        $application = $this->seedUserWithApplication($user);

        JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $user->id,
            'title' => 'Screen',
            'description' => null,
            'scheduled_at' => now()->addHours(2),
        ]);

        $expectedHref = route('applications.show', $application).'#scheduled-calls';

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($expectedHref, false);
    }

    public function test_banner_does_not_show_other_users_calls(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $application = $this->seedUserWithApplication($owner);

        JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $owner->id,
            'title' => 'Owner call',
            'description' => null,
            'scheduled_at' => now()->addHours(1),
        ]);

        $this->actingAs($other)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(__('Upcoming call reminder'), false);
    }
}
