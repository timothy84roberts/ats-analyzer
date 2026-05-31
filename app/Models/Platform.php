<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Favicon-based logo derived from the platform URL, or null when no URL is set.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->url)) {
            return null;
        }

        $host = parse_url($this->url, PHP_URL_HOST) ?: $this->url;
        $host = preg_replace('/^www\./i', '', $host);

        if (empty($host)) {
            return null;
        }

        return 'https://www.google.com/s2/favicons?domain=' . urlencode($host) . '&sz=64';
    }
}
