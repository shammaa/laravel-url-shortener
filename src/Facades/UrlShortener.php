<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Shammaa\LaravelUrlShortener\Models\ShortLink create(array $attributes)
 * @method static \Shammaa\LaravelUrlShortener\Models\ShortLink|null findByKey(string $key)
 * @method static string getShortUrl(\Shammaa\LaravelUrlShortener\Models\ShortLink $link)
 * @method static string|null generateQrCode(\Shammaa\LaravelUrlShortener\Models\ShortLink $link)
 * @method static bool verifyPassword(\Shammaa\LaravelUrlShortener\Models\ShortLink $link, string $password)
 * @method static void trackVisit(\Shammaa\LaravelUrlShortener\Models\ShortLink $link, array $visitData = [])
 * @method static bool isAccessible(\Shammaa\LaravelUrlShortener\Models\ShortLink $link)
 * @method static string getDestinationUrl(\Shammaa\LaravelUrlShortener\Models\ShortLink $link, array $additionalParams = [])
 *
 * @see \Shammaa\LaravelUrlShortener\Services\LinkManager
 */
class UrlShortener extends Facade
{
    /**
     * Get the registered name of the component
     */
    protected static function getFacadeAccessor(): string
    {
        return \Shammaa\LaravelUrlShortener\Services\LinkManager::class;
    }
}
