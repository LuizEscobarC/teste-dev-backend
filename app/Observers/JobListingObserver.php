<?php

namespace App\Observers;

use App\Models\JobListing;
use Illuminate\Support\Facades\Cache;

class JobListingObserver
{
    public function created(JobListing $jobListing): void
    {
        $this->clearJobListingCache();
    }

    public function updated(JobListing $jobListing): void
    {
        $this->clearJobListingCache();
    }

    public function deleted(JobListing $jobListing): void
    {
        $this->clearJobListingCache();
    }

    public function restored(JobListing $jobListing): void
    {
        $this->clearJobListingCache();
    }

    public function forceDeleted(JobListing $jobListing): void
    {
        $this->clearJobListingCache();
    }
    
    private function clearJobListingCache(): void
    {
        Cache::flush();
    }
}
