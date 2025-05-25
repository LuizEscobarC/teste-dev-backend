<?php

namespace App\Providers;

use App\Models\JobListing;
use App\Policies\JobListingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        JobListing::class => JobListingPolicy::class,
    ];


    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
