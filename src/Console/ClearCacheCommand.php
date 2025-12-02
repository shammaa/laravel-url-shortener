<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'url-shortener:clear-cache';

    protected $description = 'Clear the URL shortener cache';

    public function handle(): int
    {
        $prefix = config('url-shortener.cache.prefix', 'url_shortener');
        
        // This is a simple implementation - in production you might want more specific cache clearing
        Cache::flush();
        
        $this->info('URL shortener cache cleared successfully!');
        
        return Command::SUCCESS;
    }
}
