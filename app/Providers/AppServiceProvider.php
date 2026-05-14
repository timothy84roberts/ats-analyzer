<?php

namespace App\Providers;

use App\Models\JobApplication;
use App\Observers\JobApplicationObserver;
use App\Services\Ats\Providers\ApyHubAtsProvider;
use App\Services\Ats\Providers\AtsProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AtsProvider::class, ApyHubAtsProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JobApplication::observe(JobApplicationObserver::class);
    }
}
