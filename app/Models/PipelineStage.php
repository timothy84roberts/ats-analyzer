<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use HasFactory;

    public const SLUG_RESUME_SUBMITTED = 'resume_submitted';

    protected $fillable = [
        'slug',
        'label',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(JobApplicationStageHistory::class);
    }

    /**
     * First pipeline stage for a newly logged application (before any company feedback).
     */
    public static function defaultIdForNewApplication(): ?int
    {
        return static::query()
            ->where('slug', self::SLUG_RESUME_SUBMITTED)
            ->value('id')
            ?? static::query()->orderBy('sort_order')->orderBy('id')->value('id');
    }
}
