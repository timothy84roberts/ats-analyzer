<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialSetting extends Model
{
    protected $fillable = [
        'user_id',
        'year_month',
        'default_remaining',
        'additional_remaining',
    ];

    protected $casts = [
        'default_remaining' => 'decimal:2',
        'additional_remaining' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
