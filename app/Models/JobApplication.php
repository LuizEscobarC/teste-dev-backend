<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'job_listing_id',
        'user_id',
        'cover_letter',
        'resume',
        'status',
        'additional_info',
        'notes'
    ];
    
    protected $casts = [
        'additional_info' => 'array',
    ];
    
    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
