<?php

declare(strict_types=1);

use Shammaa\LaravelUrlShortener\Facades\UrlShortener;
use Shammaa\LaravelUrlShortener\Models\ShortLink;

if (!function_exists('short_url')) {
    /**
     * Create a short URL
     *
     * @param string $url
     * @param array $options
     * @return ShortLink
     */
    function short_url(string $url, array $options = []): ShortLink
    {
        return UrlShortener::create(array_merge([
            'destination_url' => $url,
        ], $options));
    }
}

if (!function_exists('short_url_key')) {
    /**
     * Get short URL by key
     *
     * @param string $key
     * @return string
     */
    function short_url_key(string $key): string
    {
        $link = UrlShortener::findByKey($key);
        
        if (!$link) {
            return '';
        }

        return UrlShortener::getShortUrl($link);
    }
}