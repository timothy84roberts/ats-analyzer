<?php

namespace App\Providers;

use App\Models\Country;
use App\Models\JobApplication;
use App\Models\Platform;
use App\Models\User;
use App\Policies\CountryPolicy;
use App\Policies\JobApplicationPolicy;
use App\Policies\PlatformPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        JobApplication::class => JobApplicationPolicy::class,
        Country::class => CountryPolicy::class,
        Platform::class => PlatformPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Countries & platforms: any signed-in user (shared reference lists for applications).
        Gate::define('manage-reference-data', fn (User $user): bool => true);

        Gate::define('use-ats-lab', fn (User $user): bool => (bool) config('ats.enabled') && $user->is_ats_lab_allowed);
    }
}
