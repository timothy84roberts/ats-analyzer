<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'country_code',
        'city',
        'state',
        'birthday',
        'linkedin',
        'github',
        'x_url',
        'facebook',
        'instagram',
        'website',
        'password',
        'is_admin',
        'is_ats_lab_allowed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, mixed>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'is_admin' => 'boolean',
        'is_ats_lab_allowed' => 'boolean',
    ];

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function jobApplicationCalls(): HasMany
    {
        return $this->hasMany(JobApplicationCall::class);
    }

    public function atsAnalysisRuns(): HasMany
    {
        return $this->hasMany(AtsAnalysisRun::class);
    }
}
