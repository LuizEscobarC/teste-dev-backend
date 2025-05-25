<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Filters\JobApplicationFilter;
use App\Http\Requests\JobApplicationFilterRequest;
use App\Http\Resources\JobApplicationResource;
use App\Http\Resources\JobApplicationCollection;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JobApplicationService
{
    public function getPaginatedJobApplications(JobApplicationFilterRequest $request): JobApplicationCollection
    {
        $user = $request->user();
        $filters = $request->validated();

        $cacheKey = 'job_applications:' . md5(json_encode([
            'filters' => $filters,
            'user_id' => $user->id,
            'user_role' => $user->role->value,
        ]));

        if ($request->boolean('skip_cache')) {
            return $this->buildApplicationsQuery($request, $filters);
        }

        $tags = [
            'job_applications',
            "user_applications:{$user->id}",
            "role_applications:{$user->role->value}",
        ];
        
        if (isset($filters['status'])) {
            $tags[] = "status_applications:{$filters['status']}";
        }
        
        if (isset($filters['jobListingId'])) {
            $jobListingId = is_array($filters['jobListingId']) ? $filters['jobListingId'][0] : $filters['jobListingId'];
            $tags[] = "job_listing_applications:{$jobListingId}";
        }

        return Cache::tags($tags)->remember($cacheKey, 60 * 15, function () use ($request, $filters) {
            return $this->buildApplicationsQuery($request, $filters);
        });
    }

    public function getJobApplication(string $id, Request $request): JobApplicationResource
    {
        $user = $request->user();
        $cacheKey = 'job_application:' . $id . ':' . md5(serialize([
            'user_id' => $user->id,
            'includes' => $request->only(['include_job_listing', 'include_user']),
        ]));

        return Cache::tags([
            'job_applications',
            "job_application:{$id}",
            "user_applications:{$user->id}",
        ])->remember($cacheKey, 60 * 30, function () use ($id, $request) {
            $query = JobApplication::query();
            
            if ($request->has('include_job_listing')) {
                $query->with('jobListing');
            }
            
            if ($request->has('include_user') && $request->user()->isRecruiter()) {
                $query->with('user');
            }
            
            $jobApplication = $query->find($id);
            if (!$jobApplication) {
                throw new ModelNotFoundException("Aplicação de emprego [$id] não encontrada.");
            }
            
            return new JobApplicationResource($jobApplication);
        });
    }

    public function createJobApplication(array $data, User $user): JobApplicationResource
    {
        $jobListing = JobListing::where('id', $data['job_listing_id'])
            ->where('is_active', true)
            ->first();
            
        if (!$jobListing) {
            throw new ModelNotFoundException('Listagem de empregos não encontrada ou inativa');
        }
        
        if ($jobListing->expiration_date && $jobListing->expiration_date->isPast()) {
            throw new \InvalidArgumentException('A lista de empregos expirou');
        }
        
        $existingApplication = JobApplication::where('user_id', $user->id)
            ->where('job_listing_id', $data['job_listing_id'])
            ->exists();
            
        if ($existingApplication) {
            throw new \InvalidArgumentException('Você já se inscreveu a este trabalho');
        }
        
        if ($jobListing->user_id === $user->id) {
            throw new \InvalidArgumentException('Não pode se inscrever na sua própria listagem de empregos');
        }
        
        $data['user_id'] = $user->id;
        $data['status'] = ApplicationStatus::PENDING->value;
        
        $jobApplication = JobApplication::create($data);
        
        return new JobApplicationResource($jobApplication);
    }

    public function updateJobApplication(string $id, array $data, User $user): JobApplicationResource
    {
        $jobApplication = JobApplication::find($id);
        if (!$jobApplication) {
            throw new ModelNotFoundException("Aplicação de emprego [$id] não encontrada.");
        }
        
        if ($user->isCandidate()) {
            if (!$jobApplication->canBeUpdatedByCandidate()) {
                throw new \InvalidArgumentException('A aplicação de emprego não pode ser atualizado no status atual');
            }
            $allowedFields = ['cover_letter', 'resume', 'additional_info'];
            $data = array_intersect_key($data, array_flip($allowedFields));
            
        } elseif ($user->isRecruiter()) {
            if (isset($data['status'])) {
                $newStatus = ApplicationStatus::tryFrom($data['status']);
                if ($newStatus && !$this->isValidStatusTransition($jobApplication->status, $newStatus)) {
                    throw new \InvalidArgumentException('Invalid status transition');
                }
            }
            $allowedFields = ['status', 'notes'];
            $data = array_intersect_key($data, array_flip($allowedFields));
        }
        
        $jobApplication->update($data);
        
        return new JobApplicationResource($jobApplication);
    }

    public function withdrawJobApplication(string $id, User $user): JobApplicationResource
    {
        $jobApplication = JobApplication::find($id);
        if (!$jobApplication) {
            throw new ModelNotFoundException("Aplicação de emprego [$id] não encontrada.");
        }
        
        if (!$jobApplication->canBeWithdrawn()) {
            throw new \InvalidArgumentException('O pedido não pode ser retirado no status atual');
        }
        
        $jobApplication->update(['status' => ApplicationStatus::WITHDRAWN]);
        
        return new JobApplicationResource($jobApplication);
    }

    public function deleteJobApplication(string $id, User $user): bool
    {
        $jobApplication = JobApplication::find($id);
        if (!$jobApplication) {
            throw new ModelNotFoundException("Aplicação de emprego [$id] não encontrada.");
        }
        
        return $jobApplication->delete();
    }

    public function pauseJobApplications(JobListing $jobListing): int
    {
        return JobApplication::where('job_listing_id', $jobListing->id)
            ->whereIn('status', [ApplicationStatus::PENDING, ApplicationStatus::REVIEWED])
            ->update(['status' => ApplicationStatus::PENDING]);
    }

    public function resumeJobApplications(JobListing $jobListing): int
    {
        return JobApplication::where('job_listing_id', $jobListing->id)
            ->where('status', ApplicationStatus::PENDING)
            ->count();
    }

    private function buildApplicationsQuery(JobApplicationFilterRequest $request, array $filters = []): JobApplicationCollection
    {
        $user = $request->user();
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $perPage = $request->input('per_page', 15);
        
        $query = JobApplication::filter(new JobApplicationFilter($filters));
        
        if ($user->isRecruiter() && !isset($filters['jobListingId'])) {
            $query->forRecruiter($user->id);
        } elseif ($user->isCandidate() && !isset($filters['userId'])) {
            $query->forCandidate($user->id);
        }
        
        if ($request->boolean('include_job_listing')) {
            $query->with('jobListing');
        }
        
        if ($request->boolean('include_user') && $user->isRecruiter()) {
            $query->with('user');
        }
        
        $jobApplications = $query->orderBy($orderBy, $orderDirection)->paginate($perPage);
        
        return new JobApplicationCollection($jobApplications);
    }

    private function isValidStatusTransition(ApplicationStatus $currentStatus, ApplicationStatus $newStatus): bool
    {
        $allowedTransitions = [
            ApplicationStatus::PENDING->value => [ApplicationStatus::REVIEWED, ApplicationStatus::INTERVIEWING, ApplicationStatus::REJECTED],
            ApplicationStatus::REVIEWED->value => [ApplicationStatus::INTERVIEWING, ApplicationStatus::REJECTED],
            ApplicationStatus::INTERVIEWING->value => [ApplicationStatus::ACCEPTED, ApplicationStatus::REJECTED],
            ApplicationStatus::REJECTED->value => [],
            ApplicationStatus::ACCEPTED->value => [],
            ApplicationStatus::WITHDRAWN->value => [],
        ];
        
        return in_array($newStatus, $allowedTransitions[$currentStatus->value] ?? []);
    }
}
