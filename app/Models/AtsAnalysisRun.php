<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtsAnalysisRun extends Model
{
    protected $fillable = [
        'user_id',
        'job_application_id',
        'status',
        'score',
        'result_payload',
        'resume_path',
    ];

    protected $casts = [
        'result_payload' => 'array',
        'score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
