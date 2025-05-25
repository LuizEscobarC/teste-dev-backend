<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobApplicationStoreRequest;
use App\Http\Requests\JobApplicationUpdateRequest;
use App\Http\Requests\JobApplicationFilterRequest;
use App\Models\JobApplication;
use App\Services\JobApplicationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class JobApplicationController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        private JobApplicationService $jobApplicationService
    ) {}

    /**
     * Display a listing of job applications
     */
    public function index(JobApplicationFilterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', JobApplication::class);
        
        $applications = $this->jobApplicationService->getPaginatedJobApplications($request);
        
        return response()->json($applications);
    }

    /**
     * Store a newly created job application
     */
    public function store(JobApplicationStoreRequest $request): JsonResponse
    {
        $this->authorize('create', JobApplication::class);
        
        $jobApplication = $this->jobApplicationService->createJobApplication(
            $request->validated(),
            $request->user()
        );
        
        return response()->json(['data' => $jobApplication], 201);
    }

    /**
     * Display the specified job application
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $jobApplication = JobApplication::find($id);
        if (!$jobApplication) {
            throw new ModelNotFoundException('Aplicação de emprego não encontrada');
        }
        $this->authorize('view', $jobApplication);
        
        $applicationResource = $this->jobApplicationService->getJobApplication($id, $request);
        
        return response()->json(['data' => $applicationResource]);
    }

    /**
     * Update the specified job application
     */
    public function update(JobApplicationUpdateRequest $request, string $id): JsonResponse
    {
        $jobApplication = JobApplication::find($id);
        if (!$jobApplication) {
            throw new ModelNotFoundException('Aplicação de emprego não encontrada');
        }
        $this->authorize('update', $jobApplication);
        
        $updatedApplication = $this->jobApplicationService->updateJobApplication(
            $id,
            $request->validated(),
            $request->user()
        );
        
        return response()->json(['data' => $updatedApplication]);
    }

    /**
     * Withdraw job application (soft status change)
     */
    public function withdraw(Request $request, string $id): JsonResponse
    {
        $jobApplication = JobApplication::find($id);
        if (!$jobApplication) {
            throw new ModelNotFoundException('Aplicação de emprego não encontrada');
        }
        $this->authorize('withdraw', $jobApplication);
        
        $withdrawnApplication = $this->jobApplicationService->withdrawJobApplication($id, $request->user());
        
        return response()->json([
            'message' => 'Aplicação retirada com sucesso',
            'data' => $withdrawnApplication
        ]);
    }

    /**
     * Remove the specified job application
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $jobApplication = JobApplication::find($id);
        if (!$jobApplication) {
            throw new ModelNotFoundException('Aplicação de emprego não encontrada');
        }
        $this->authorize('delete', $jobApplication);
        
        $this->jobApplicationService->deleteJobApplication($id, $request->user());
        
        return response()->json(['message' => 'Pedido de emprego excluído com sucesso']);
    }
}
