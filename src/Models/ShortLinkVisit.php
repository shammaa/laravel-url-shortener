<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortLinkVisit extends Model
{
    protected $fillable = [
        'short_link_id',
        'ip_address',
        'country',
        'country_code',
        'city',
        'region',
        'latitude',
        'longitude',
        'user_agent',
        'device_type',
        'device_name',
        'platform',
        'platform_version',
        'browser',
        'browser_version',
        'is_bot',
        'is_mobile',
        'is_tablet',
        'referer_url',
        'referer_domain',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'query_parameters',
        'language',
        'timezone',
        'session_id',
        'visited_at',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'visited_at' => 'datetime',
        'utm_source' => 'array',
        'utm_medium' => 'array',
        'utm_campaign' => 'array',
        'utm_term' => 'array',
        'utm_content' => 'array',
        'query_parameters' => 'array',
    ];

    /**
     * Get the table associated with the model
     */
    public function getTable(): string
    {
        return config('url-shortener.connection')
            ? config('database.connections.' . config('url-shortener.connection') . '.prefix', '') . 'short_link_visits'
            : 'short_link_visits';
    }

    /**
     * Get the connection name for the model
     */
    public function getConnectionName(): ?string
    {
        return config('url-shortener.connection');
    }

    /**
     * Relationship: Short Link
     */
    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(ShortLink::class);
    }

    /**
     * Scope: Today's visits
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', today());
    }

    /**
     * Scope: This week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('visited_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope: This month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('visited_at', now()->month)
            ->whereYear('visited_at', now()->year);
    }

    /**
     * Scope: By country
     */
    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Scope: By device type
     */
    public function scopeByDeviceType($query, string $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope: Unique visits (by IP)
     */
    public function scopeUnique($query)
    {
        return $query->distinct('ip_address');
    }
}
