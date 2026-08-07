<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobApplicationCall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_users_index(): void
    {
        $this->get(route('users.index'))->assertRedirect();
    }

    public function test_admin_can_list_managed_users_only(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Admin Person']);
        $managed = User::factory()->create(['is_admin' => false, 'name' => 'Managed Person']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Managed Person')
            ->assertViewHas('users', function ($users) {
                return $users->count() === 1
                    && $users->first()->name === 'Managed Person';
            });
    }

    public function test_admin_can_create_managed_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Alex Applicant',
                'email' => 'alex@example.com',
                'phone' => '+1 555 0100',
                'address' => '123 Main St',
                'country_code' => 'US',
                'city' => 'Austin',
                'state' => 'TX',
                'birthday' => '1990-05-15',
                'linkedin' => 'https://linkedin.com/in/alex',
                'github' => 'https://github.com/alex',
                'x_url' => 'https://x.com/alex',
                'facebook' => 'https://facebook.com/alex',
                'instagram' => 'https://instagram.com/alex',
                'website' => 'https://alex.dev',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Alex Applicant',
            'email' => 'alex@example.com',
            'phone' => '+1 555 0100',
            'address' => '123 Main St',
            'country_code' => 'US',
            'city' => 'Austin',
            'state' => 'TX',
            'linkedin' => 'https://linkedin.com/in/alex',
            'github' => 'https://github.com/alex',
            'x_url' => 'https://x.com/alex',
            'facebook' => 'https://facebook.com/alex',
            'instagram' => 'https://instagram.com/alex',
            'website' => 'https://alex.dev',
            'is_admin' => false,
        ]);

        $created = User::query()->where('email', 'alex@example.com')->first();
        $this->assertNotNull($created);
        $this->assertSame('1990-05-15', $created->birthday?->format('Y-m-d'));
    }

    public function test_admin_can_update_managed_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create([
            'is_admin' => false,
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $managed), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'phone' => '+81 90 1111 2222',
                'address' => '1-2-3 Chuo',
                'country_code' => 'JP',
                'city' => 'Tokyo',
                'state' => '',
                'birthday' => '1988-01-02',
                'linkedin' => 'https://linkedin.com/in/new',
                'github' => '',
                'x_url' => '',
                'facebook' => '',
                'instagram' => '',
                'website' => 'https://new.example',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $managed->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '+81 90 1111 2222',
            'city' => 'Tokyo',
            'state' => null,
            'country_code' => 'JP',
            'linkedin' => 'https://linkedin.com/in/new',
            'github' => null,
            'website' => 'https://new.example',
            'is_admin' => false,
        ]);
    }

    public function test_admin_cannot_edit_admin_account_via_users_ui(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('users.edit', $otherAdmin))
            ->assertNotFound();
    }

    public function test_admin_can_delete_managed_user_without_related_records(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $managed))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $managed->id]);
    }

    public function test_cannot_delete_user_with_job_applications(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false]);
        JobApplication::factory()->create(['user_id' => $managed->id]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $managed))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('users', ['id' => $managed->id]);
    }

    public function test_cannot_delete_user_with_schedule_items(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $managed = User::factory()->create(['is_admin' => false]);
        $other = User::factory()->create(['is_admin' => false]);
        $application = JobApplication::factory()->create(['user_id' => $other->id]);

        JobApplicationCall::create([
            'job_application_id' => $application->id,
            'user_id' => $managed->id,
            'title' => 'Screening call',
            'description' => null,
            'scheduled_at' => now()->addDay(),
        ]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $managed))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('users', ['id' => $managed->id]);
    }
}
