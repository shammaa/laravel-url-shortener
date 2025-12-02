<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortLinkAnalytics extends Model
{
    protected $fillable = [
        'short_link_id',
        'date',
        'total_clicks',
        'unique_clicks',
        'unique_visitors',
        'clicks_by_country',
        'clicks_by_city',
        'clicks_by_device',
        'clicks_by_platform',
        'clicks_by_browser',
        'clicks_by_referer',
        'clicks_by_utm_source',
        'clicks_by_utm_medium',
        'clicks_by_utm_campaign',
        'clicks_by_hour',
    ];

    protected $casts = [
        'date' => 'date',
        'total_clicks' => 'integer',
        'unique_clicks' => 'integer',
        'unique_visitors' => 'integer',
        'clicks_by_country' => 'array',
        'clicks_by_city' => 'array',
        'clicks_by_device' => 'array',
        'clicks_by_platform' => 'array',
        'clicks_by_browser' => 'array',
        'clicks_by_referer' => 'array',
        'clicks_by_utm_source' => 'array',
        'clicks_by_utm_medium' => 'array',
        'clicks_by_utm_campaign' => 'array',
        'clicks_by_hour' => 'array',
    ];

    /**
     * Get the table associated with the model
     */
    public function getTable(): string
    {
        return config('url-shortener.connection')
            ? config('database.connections.' . config('url-shortener.connection') . '.prefix', '') . 'short_link_analytics'
            : 'short_link_analytics';
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
}
