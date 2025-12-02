<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Services;

use Shammaa\LaravelUrlShortener\Models\ShortLink;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LinkManager
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create a new short link
     */
    public function create(array $attributes): ShortLink
    {
        // Generate unique key
        $key = $this->generateUniqueKey($attributes['key'] ?? null);

        // Handle password protection
        if (!empty($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
            $attributes['password_protected'] = true;
        }

        // Handle expiry
        if (!empty($attributes['expires_in_days'])) {
            $attributes['expires_at'] = now()->addDays($attributes['expires_in_days']);
        }

        // Set default tracking options
        $attributes = array_merge([
            'track_visits' => $this->config['track_visits'] ?? true,
            'track_ip_address' => $this->config['track_ip_address'] ?? true,
            'track_user_agent' => $this->config['track_user_agent'] ?? true,
            'track_referer' => $this->config['track_referer'] ?? true,
            'track_geo' => $this->config['track_geo'] ?? false,
            'redirect_status_code' => $this->config['redirect_status_code'] ?? '302',
            'is_active' => true,
            'activated_at' => now(),
        ], $attributes);

        // Handle UTM parameters
        if (!empty($attributes['utm_parameters'])) {
            $attributes['utm_parameters'] = json_encode($attributes['utm_parameters']);
        }

        // Create the link
        $link = ShortLink::create(array_merge($attributes, [
            'key' => $key,
            'clicks_count' => 0,
        ]));

        // Generate QR code if enabled
        if ($this->config['qr_code']['enabled'] ?? true) {
            $this->generateQrCode($link);
        }

        // Clear cache
        $this->clearCache($link);

        return $link;
    }

    /**
     * Generate unique key for short link
     */
    protected function generateUniqueKey(?string $customKey = null): string
    {
        if ($customKey) {
            // Validate custom key is unique
            if (ShortLink::where('key', $customKey)->exists()) {
                throw new \Exception("Key '{$customKey}' already exists");
            }
            return $customKey;
        }

        $length = $this->config['key_length'] ?? 6;
        $chars = $this->config['key_chars'] ?? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxAttempts = 100;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $key = '';
            for ($j = 0; $j < $length; $j++) {
                $key .= $chars[random_int(0, strlen($chars) - 1)];
            }

            if (!ShortLink::where('key', $key)->exists()) {
                return $key;
            }
        }

        throw new \Exception('Failed to generate unique key after ' . $maxAttempts . ' attempts');
    }

    /**
     * Find link by key
     */
    public function findByKey(string $key): ?ShortLink
    {
        $cacheKey = $this->getCacheKey("link:{$key}");

        if ($this->isCacheEnabled()) {
            return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($key) {
                return ShortLink::where('key', $key)->first();
            });
        }

        return ShortLink::where('key', $key)->first();
    }

    /**
     * Get full short URL
     */
    public function getShortUrl(ShortLink $link): string
    {
        $domain = $this->config['domain'] ?? config('app.url');
        $prefix = $this->config['prefix'] ?? 's';

        $domain = rtrim($domain, '/');
        $prefix = ltrim($prefix, '/');

        return "{$domain}/{$prefix}/{$link->key}";
    }

    /**
     * Generate QR code for link
     */
    public function generateQrCode(ShortLink $link): ?string
    {
        if (!($this->config['qr_code']['enabled'] ?? true)) {
            return null;
        }

        try {
            $qrConfig = $this->config['qr_code'] ?? [];
            $url = $this->getShortUrl($link);
            $size = $qrConfig['size'] ?? 200;
            $format = $qrConfig['format'] ?? 'svg';
            $margin = $qrConfig['margin'] ?? 1;
            $errorCorrection = $qrConfig['error_correction'] ?? 'M';

            // Generate QR code
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format($format)
                ->size($size)
                ->margin($margin)
                ->errorCorrection($errorCorrection)
                ->generate($url);

            // Save to storage
            $filename = "qr-codes/{$link->key}.{$format}";
            Storage::disk('public')->put($filename, $qrCode);

            // Update link
            $link->update(['qr_code_path' => $filename]);

            return Storage::disk('public')->url($filename);
        } catch (\Exception $e) {
            // Fail silently if QR code generation fails
            return null;
        }
    }

    /**
     * Verify password
     */
    public function verifyPassword(ShortLink $link, string $password): bool
    {
        if (!$link->password_protected || !$link->password) {
            return true; // No password required
        }

        return Hash::check($password, $link->password);
    }

    /**
     * Track visit
     */
    public function trackVisit(ShortLink $link, array $visitData = []): void
    {
        if (!$link->track_visits) {
            return;
        }

        $visit = $link->visits()->create(array_merge([
            'visited_at' => now(),
        ], $visitData));

        // Update link statistics
        $link->increment('clicks_count');
        
        if (!$link->first_clicked_at) {
            $link->update(['first_clicked_at' => now()]);
        }
        
        $link->update(['last_clicked_at' => now()]);

        // Clear cache
        $this->clearCache($link);
    }

    /**
     * Check if link is active and accessible
     */
    public function isAccessible(ShortLink $link): bool
    {
        if (!$link->is_active) {
            return false;
        }

        // Check expiry
        if ($link->expires_at && $link->expires_at->isPast()) {
            return false;
        }

        // Check activation
        if ($link->activated_at && $link->activated_at->isFuture()) {
            return false;
        }

        // Check click limit
        if ($link->click_limit && $link->clicks_count >= $link->click_limit) {
            return false;
        }

        return true;
    }

    /**
     * Get destination URL with UTM parameters
     */
    public function getDestinationUrl(ShortLink $link, array $additionalParams = []): string
    {
        $url = $link->destination_url;

        // Add UTM parameters if enabled
        if (($this->config['utm']['enabled'] ?? true) && $link->utm_parameters) {
            $utmParams = is_string($link->utm_parameters) 
                ? json_decode($link->utm_parameters, true) 
                : $link->utm_parameters;

            if ($utmParams) {
                $additionalParams = array_merge($utmParams, $additionalParams);
            }
        }

        // Add default UTM if hidden tracking is enabled
        if (($this->config['utm']['hidden'] ?? true) && ($this->config['utm']['enabled'] ?? true)) {
            $additionalParams['utm_source'] = $additionalParams['utm_source'] ?? ($this->config['utm']['source'] ?? 'url-shortener');
            $additionalParams['utm_medium'] = $additionalParams['utm_medium'] ?? ($this->config['utm']['medium'] ?? 'short-link');
        }

        if (!empty($additionalParams)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($additionalParams);
        }

        return $url;
    }

    /**
     * Clear cache for link
     */
    protected function clearCache(ShortLink $link): void
    {
        if ($this->isCacheEnabled()) {
            Cache::forget($this->getCacheKey("link:{$link->key}"));
        }
    }

    /**
     * Check if cache is enabled
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? true;
    }

    /**
     * Get cache TTL
     */
    protected function getCacheTtl(): int
    {
        return $this->config['cache']['ttl'] ?? 3600;
    }

    /**
     * Get cache key
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = $this->config['cache']['prefix'] ?? 'url_shortener';
        return "{$prefix}:{$key}";
    }
}
