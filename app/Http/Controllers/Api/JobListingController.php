<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobListingFilterRequest;
use App\Http\Requests\JobListingStoreRequest;
use App\Http\Requests\JobListingUpdateRequest;
use App\Models\JobListing;
use App\Services\JobListingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    use AuthorizesRequests;
    
    private JobListingService $jobListingService;

    public function __construct(JobListingService $jobListingService)
    {
        $this->jobListingService = $jobListingService;
    }

    /**
     * @param JobListingFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(JobListingFilterRequest $request)
    {
        return $this->jobListingService->getPaginatedJobListings($request);
    }

    /**
     * @param JobListingStoreRequest $request
     * @return \App\Http\Resources\JobListingResource
     */
    public function store(JobListingStoreRequest $request)
    {
        $this->authorize('create', JobListing::class);
        
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        
        return $this->jobListingService->createJobListing($data);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return \App\Http\Resources\JobListingResource
     */
    public function show(string $id, Request $request)
    {
        return $this->jobListingService->getJobListingById($id, $request);
    }

    /**
     * @param JobListingUpdateRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\JobListingResource
     */
    public function update(JobListingUpdateRequest $request, string $id)
    {
        $jobListing = JobListing::findOrFail($id);
        $this->authorize('update', $jobListing);
        
        $data = $request->validated();
        return $this->jobListingService->updateJobListing($id, $data);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        $jobListing = JobListing::findOrFail($id);
        $this->authorize('delete', $jobListing);
        
        $this->jobListingService->deleteJobListing($id);
        
        return response()->json(['message' => 'Job listing deleted successfully'], 200);
    }
}
