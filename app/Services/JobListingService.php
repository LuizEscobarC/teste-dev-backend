<?php

namespace App\Services;

use App\Filters\JobListingFilter;
use App\Http\Requests\JobListingFilterRequest;
use App\Http\Resources\JobListingCollection;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JobListingService
{

    public function getPaginatedJobListings(JobListingFilterRequest $request): JobListingCollection
    {
        $cacheKey = 'job_listings:' . md5(json_encode($request->validated()));
        $cacheTTL = 60 * 15;
        
        if (!$request->has('skip_cache') && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $perPage = $request->input('per_page', 15);
        
        $query = JobListing::filter(new JobListingFilter($request));
        
        if ($request->boolean('include_user')) {
            $query->with('user');
        }
        
        if ($request->boolean('include_applications')) {
            $query->with('applications');
        }
        
        $jobListings = $query->orderBy($orderBy, $orderDirection)->paginate($perPage);
        $response = new JobListingCollection($jobListings);
        
        Cache::put($cacheKey, $response, $cacheTTL);
        
        return $response;
    }

    public function getJobListingById(string $id, Request $request): JobListingResource
    {
        $query = JobListing::query();
        
        if ($request->has('include_user')) {
            $query->with('user');
        }
        
        if ($request->has('include_applications')) {
            $query->with('applications');
        }
        
        $jobListing = $query->find($id);
        if (!$jobListing) {
            throw new ModelNotFoundException("Vaga de emprego [$id] não encontrada.");
        }
        
        return new JobListingResource($jobListing);
    }

    public function createJobListing(array $data): JobListingResource
    {
        $jobListing = JobListing::create($data);
        
        return new JobListingResource($jobListing);
    }

    public function updateJobListing(string $id, array $data): JobListingResource
    {
        $jobListing = JobListing::find($id);
        if (!$jobListing) {
            throw new ModelNotFoundException("Vaga de emprego [$id] não encontrada.");
        }
        $jobListing->update($data);
        
        return new JobListingResource($jobListing);
    }

    public function checkUserAuthorization(string $jobListingId, int $userId): bool
    {
        $jobListing = JobListing::find($jobListingId);
        if (!$jobListing) {
            throw new ModelNotFoundException("Vaga de emprego [$jobListingId] não encontrada.");
        }
        return $userId === $jobListing->user_id;
    }

    public function deleteJobListing(string $id): bool
    {
        $jobListing = JobListing::find($id);
        if (!$jobListing) {
            throw new ModelNotFoundException("Vaga de emprego [$id] não encontrada.");
        }
        return $jobListing->delete();
    }
}
