<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class JobApplication extends Model
{
    use HasFactory;

    public const OUTCOME_WAITING = 'waiting';

    public const OUTCOME_REJECTED = 'rejected';

    public const OUTCOME_INTERVIEW = 'interview';

    public const OUTCOME_SUCCESS = 'success';

    /**
     * Canonical order; default outcome for new applications is {@see OUTCOME_WAITING}.
     *
     * @return list<string>
     */
    public static function outcomeStatuses(): array
    {
        return [
            self::OUTCOME_WAITING,
            self::OUTCOME_REJECTED,
            self::OUTCOME_INTERVIEW,
            self::OUTCOME_SUCCESS,
        ];
    }

    /**
     * @return array<string, array{icon: string}>
     */
    public static function outcomeStatPresentation(): array
    {
        return [
            self::OUTCOME_WAITING => ['icon' => 'muted'],
            self::OUTCOME_REJECTED => ['icon' => 'warn'],
            self::OUTCOME_INTERVIEW => ['icon' => 'info'],
            self::OUTCOME_SUCCESS => ['icon' => 'money'],
        ];
    }

    protected $attributes = [
        'outcome_status' => self::OUTCOME_WAITING,
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'resume_path',
        'notes',
        'outcome_status',
        'pipeline_stage_id',
        'rejection_reason',
        'country_id',
        'company_name',
        'platform_id',
        'analysis_percentage',
        'applied_on',
        'meta',
    ];

    protected $casts = [
        'applied_on' => 'date',
        'meta' => 'array',
        'analysis_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (JobApplication $model): void {
            if ($model->outcome_status === null || $model->outcome_status === '') {
                $model->outcome_status = self::OUTCOME_WAITING;
            }
        });
    }

    public function isRejected(): bool
    {
        return $this->outcome_status === self::OUTCOME_REJECTED;
    }

    public function hasResume(): bool
    {
        return filled($this->resume_path);
    }

    public function deleteResumeFromDisk(): void
    {
        if (! filled($this->resume_path)) {
            return;
        }
        Storage::disk('local')->delete($this->resume_path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(JobApplicationStageHistory::class)->orderBy('entered_at');
    }

    public function atsAnalysisRuns(): HasMany
    {
        return $this->hasMany(AtsAnalysisRun::class);
    }
}
