<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class ShortLink extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'destination_url',
        'title',
        'description',
        'password',
        'password_protected',
        'activated_at',
        'expires_at',
        'is_active',
        'click_limit',
        'clicks_count',
        'track_visits',
        'track_ip_address',
        'track_user_agent',
        'track_referer',
        'track_geo',
        'utm_parameters',
        'utm_hidden',
        'redirect_status_code',
        'custom_domain',
        'qr_code_path',
        'user_id',
        'user_type',
        'short_linkable_type',
        'short_linkable_id',
        'metadata',
        'tags',
        'group',
        'first_clicked_at',
        'last_clicked_at',
    ];

    protected $casts = [
        'password_protected' => 'boolean',
        'is_active' => 'boolean',
        'track_visits' => 'boolean',
        'track_ip_address' => 'boolean',
        'track_user_agent' => 'boolean',
        'track_referer' => 'boolean',
        'track_geo' => 'boolean',
        'utm_hidden' => 'boolean',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'first_clicked_at' => 'datetime',
        'last_clicked_at' => 'datetime',
        'click_limit' => 'integer',
        'clicks_count' => 'integer',
        'utm_parameters' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
    ];

    /**
     * Get the table associated with the model
     */
    public function getTable(): string
    {
        return config('url-shortener.connection')
            ? config('database.connections.' . config('url-shortener.connection') . '.prefix', '') . 'short_links'
            : 'short_links';
    }

    /**
     * Get the connection name for the model
     */
    public function getConnectionName(): ?string
    {
        return config('url-shortener.connection');
    }

    /**
     * Relationship: Visits
     */
    public function visits(): HasMany
    {
        return $this->hasMany(ShortLinkVisit::class);
    }

    /**
     * Relationship: Analytics
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(ShortLinkAnalytics::class);
    }

    /**
     * Relationship: User (polymorphic)
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relationship: ShortLinkable (polymorphic - the model this short link belongs to)
     */
    public function shortLinkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if link is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if link is active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->activated_at && $this->activated_at->isFuture()) {
            return false;
        }

        if ($this->click_limit && $this->clicks_count >= $this->click_limit) {
            return false;
        }

        return true;
    }

    /**
     * Get short URL
     */
    public function getShortUrlAttribute(): string
    {
        $manager = app(\Shammaa\LaravelUrlShortener\Services\LinkManager::class);
        return $manager->getShortUrl($this);
    }

    /**
     * Scope: Active links
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('activated_at')
                  ->orWhere('activated_at', '<=', now());
            });
    }

    /**
     * Scope: Expired links
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $user)
    {
        return $query->where('user_id', $user->id ?? $user)
            ->where('user_type', get_class($user));
    }
}
