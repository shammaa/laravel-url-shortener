<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Traits;

use Shammaa\LaravelUrlShortener\Models\ShortLink;
use Shammaa\LaravelUrlShortener\Facades\UrlShortener;
use Illuminate\Support\Str;

trait HasShortLink
{
    /**
     * Convert string to hyphenated format (lowercase with dashes)
     * This method wraps Laravel's string conversion for consistent naming
     * 
     * @param string $value The string to convert
     * @return string The string in hyphenated lowercase format
     */
    protected function toHyphenatedFormat(string $value): string
    {
        return Str::kebab($value);
    }

    /**
     * Get the short link for this model
     */
    public function shortLink()
    {
        return $this->morphOne(ShortLink::class, 'short_linkable');
    }

    /**
     * Create or get short link for this model
     */
    public function createShortLink(array $options = []): ShortLink
    {
        // If short link already exists, return it
        if ($this->shortLink) {
            return $this->shortLink;
        }

        // Generate custom key if not provided
        $key = $options['key'] ?? $this->generateShortLinkKey();

        // Get destination URL
        $destinationUrl = $options['destination_url'] ?? $this->getShortLinkUrl();

        // Create short link
        $attributes = array_merge([
            'destination_url' => $destinationUrl,
            'key' => $key,
            'title' => $options['title'] ?? $this->getShortLinkTitle(),
        ], $options);

        $link = UrlShortener::create($attributes);

        // Update the polymorphic relationship
        $link->shortLinkable()->associate($this);
        $link->save();

        return $link->fresh();
    }

    /**
     * Get short link URL (override this method in your model)
     */
    public function getShortLinkUrl(): string
    {
        // Try to get route name from model name
        $routeName = $this->getShortLinkRouteName();
        
        if ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
            return route($routeName, $this->getRouteKey());
        }

        // Fallback: use model URL if exists
        if (method_exists($this, 'getUrl')) {
            return $this->getUrl();
        }

        // Last fallback
        return config('app.url') . '/' . $this->toHyphenatedFormat(class_basename($this)) . '/' . $this->getRouteKey();
    }

    /**
     * Get short link title (override this method in your model)
     */
    public function getShortLinkTitle(): ?string
    {
        // Try common title fields
        if (isset($this->title)) {
            return $this->title;
        }

        if (isset($this->name)) {
            return $this->name;
        }

        return class_basename($this) . ' #' . $this->getKey();
    }

    /**
     * Generate custom key for short link
     * Format: {prefix}-{random-code}
     * Example: post-xyz1, blog-abc2, article-def3
     * 
     * The prefix can be:
     * 1. Custom field: short_link_prefix (if exists in model)
     * 2. Model name: automatically extracted from class name
     * 
     * Override this method in your model to customize key generation
     */
    public function generateShortLinkKey(): string
    {
        // Get prefix - use custom field if available, otherwise use model name
        $prefix = $this->getShortLinkPrefix();
        
        // Generate random code
        $randomCode = $this->generateRandomCode();
        
        // Combine: prefix-randomCode
        $key = "{$prefix}-{$randomCode}";
        
        // Ensure uniqueness by checking if key exists
        $attempts = 0;
        $maxAttempts = 100;
        
        while (ShortLink::where('key', $key)->exists() && $attempts < $maxAttempts) {
            $randomCode = $this->generateRandomCode();
            $key = "{$prefix}-{$randomCode}";
            $attempts++;
        }
        
        if ($attempts >= $maxAttempts) {
            throw new \Exception("Failed to generate unique key after {$maxAttempts} attempts");
        }
        
        return $key;
    }

    /**
     * Get prefix for short link key
     * 
     * Priority order:
     * 1. Custom field: short_link_prefix (if exists in model)
     * 2. Custom method: getShortLinkPrefix() (if exists)
     * 3. Model name: automatically extracted and converted to hyphenated format
     * 
     * The model name is converted to a hyphenated, lowercase format:
     * - "BlogPost" → "blog-post"
     * - "Article" → "article"
     * - "ProductItem" → "product-item"
     * 
     * @return string The prefix in hyphenated lowercase format
     */
    protected function getShortLinkPrefix(): string
    {
        // Priority 1: Custom field in database
        if (isset($this->short_link_prefix) && !empty($this->short_link_prefix)) {
            return $this->toHyphenatedFormat($this->short_link_prefix);
        }

        // Priority 2: Custom method override
        if (method_exists($this, 'getShortLinkPrefix')) {
            return $this->toHyphenatedFormat($this->getShortLinkPrefix());
        }

        // Priority 3: Model name (default)
        // class_basename() extracts class name without namespace: "App\Models\Post" → "Post"
        // Converts to hyphenated lowercase format: "BlogPost" → "blog-post"
        return $this->toHyphenatedFormat(class_basename($this));
    }

    /**
     * Generate random code for short link key
     * Override this method to customize the random code generation
     */
    protected function generateRandomCode(): string
    {
        $config = config('url-shortener', []);
        $length = $config['model_key_length'] ?? 4; // Default 4 characters for model keys
        $chars = $config['key_chars'] ?? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $code;
    }

    /**
     * Get route name for short link URL generation
     * Override this method to specify custom route
     */
    protected function getShortLinkRouteName(): ?string
    {
        $modelName = $this->toHyphenatedFormat(Str::plural(class_basename($this)));
        
        // Try common route patterns
        $possibleRoutes = [
            "{$modelName}.show",
            Str::singular($modelName) . '.show',
        ];

        foreach ($possibleRoutes as $route) {
            if (\Illuminate\Support\Facades\Route::has($route)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Get the short URL directly
     */
    public function getShortUrlAttribute(): ?string
    {
        if (!$this->shortLink) {
            return null;
        }

        return $this->shortLink->short_url;
    }

    /**
     * Check if model has short link
     */
    public function hasShortLink(): bool
    {
        return $this->shortLink !== null;
    }
}

