<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobListing extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'company_name',
        'location',
        'type',
        'salary',
        'requirements',
        'benefits',
        'expiration_date',
        'is_active',
        'vacancies',
        'experience_level'
    ];
    
    protected $casts = [
        'requirements' => 'array',
        'benefits' => 'array',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
        'salary' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
