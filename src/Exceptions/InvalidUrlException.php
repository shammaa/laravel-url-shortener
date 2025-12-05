<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Exceptions;

use InvalidArgumentException;

class InvalidUrlException extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $url)
    {
        parent::__construct("Invalid URL: {$url}");
    }
}

