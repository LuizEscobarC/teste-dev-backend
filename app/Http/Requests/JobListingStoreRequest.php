<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class JobListingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::RECRUITER;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            // TODO: ADD ENUM LARAVEL CLASS TO CLT,PJ,Freelancer
            'type' => 'required|in:CLT,PJ,Freelancer',
            'salary' => 'nullable|numeric',
            'requirements' => 'nullable|array',
            'benefits' => 'nullable|array',
            'expiration_date' => 'nullable|date',
            'is_active' => 'boolean',
            'vacancies' => 'required|integer|min:1',
            'experience_level' => 'nullable|string|max:100',
        ];
    }
}
