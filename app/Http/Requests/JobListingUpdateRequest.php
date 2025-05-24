<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobListingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'company_name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            // TODO: ADD ENUM LARAVEL CLASS TO CLT,PJ,Freelancer
            'type' => 'sometimes|in:CLT,PJ,Freelancer',
            'salary' => 'nullable|numeric',
            'requirements' => 'nullable|array',
            'benefits' => 'nullable|array',
            'expiration_date' => 'nullable|date',
            'is_active' => 'boolean',
            'vacancies' => 'sometimes|integer|min:1',
            'experience_level' => 'nullable|string|max:100',
        ];
    }
}
