<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Domain
    |--------------------------------------------------------------------------
    |
    | The default domain to use for shortened URLs. If null, uses the current
    | application domain from config('app.url').
    |
    */
    'domain' => env('URL_SHORTENER_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Short URL Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix to use for shortened URLs. For example, if set to 's', your
    | short URLs will look like: https://example.com/s/abc123
    |
    */
    'prefix' => env('URL_SHORTENER_PREFIX', 's'),

    /*
    |--------------------------------------------------------------------------
    | Key Length
    |--------------------------------------------------------------------------
    |
    | The length of the unique key for each shortened URL.
    |
    */
    'key_length' => env('URL_SHORTENER_KEY_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | Model Key Length
    |--------------------------------------------------------------------------
    |
    | The length of the random code when using HasShortLink trait.
    | Format: {model-name}-{random-code}
    | Example: post-coco (4 chars), article-xyz123 (6 chars)
    |
    */
    'model_key_length' => env('URL_SHORTENER_MODEL_KEY_LENGTH', 4),

    /*
    |--------------------------------------------------------------------------
    | Key Characters
    |--------------------------------------------------------------------------
    |
    | The characters to use when generating keys. You can customize this to
    | exclude confusing characters like 0, O, I, l, etc.
    |
    */
    'key_chars' => env('URL_SHORTENER_KEY_CHARS', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'),

    /*
    |--------------------------------------------------------------------------
    | Track Visits
    |--------------------------------------------------------------------------
    |
    | Whether to track visits to shortened URLs by default.
    |
    */
    'track_visits' => env('URL_SHORTENER_TRACK_VISITS', true),

    /*
    |--------------------------------------------------------------------------
    | Track IP Address
    |--------------------------------------------------------------------------
    |
    | Whether to track the IP address of visitors.
    |
    */
    'track_ip_address' => env('URL_SHORTENER_TRACK_IP', true),

    /*
    |--------------------------------------------------------------------------
    | Track User Agent
    |--------------------------------------------------------------------------
    |
    | Whether to track the user agent (browser/device info) of visitors.
    |
    */
    'track_user_agent' => env('URL_SHORTENER_TRACK_USER_AGENT', true),

    /*
    |--------------------------------------------------------------------------
    | Track Referer
    |--------------------------------------------------------------------------
    |
    | Whether to track the referer URL of visitors.
    |
    */
    'track_referer' => env('URL_SHORTENER_TRACK_REFERER', true),

    /*
    |--------------------------------------------------------------------------
    | Track Geographic Location
    |--------------------------------------------------------------------------
    |
    | Whether to track geographic location (country, city) of visitors.
    | Requires IP geolocation service.
    |
    */
    'track_geo' => env('URL_SHORTENER_TRACK_GEO', false),

    /*
    |--------------------------------------------------------------------------
    | Default Redirect Status Code
    |--------------------------------------------------------------------------
    |
    | The HTTP status code to use when redirecting (301 = permanent, 302 = temporary).
    |
    */
    'redirect_status_code' => env('URL_SHORTENER_REDIRECT_CODE', 302),

    /*
    |--------------------------------------------------------------------------
    | UTM Tracking
    |--------------------------------------------------------------------------
    |
    | Configuration for UTM parameter tracking and appending.
    |
    */
    'utm' => [
        'enabled' => env('URL_SHORTENER_UTM_ENABLED', true),
        'hidden' => env('URL_SHORTENER_UTM_HIDDEN', true), // Hide UTM in analytics but track internally
        'source' => env('URL_SHORTENER_UTM_SOURCE', 'url-shortener'),
        'medium' => env('URL_SHORTENER_UTM_MEDIUM', 'short-link'),
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Code Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for QR code generation.
    |
    */
    'qr_code' => [
        'enabled' => env('URL_SHORTENER_QR_ENABLED', true),
        'size' => env('URL_SHORTENER_QR_SIZE', 200),
        'format' => env('URL_SHORTENER_QR_FORMAT', 'svg'), // svg, png
        'margin' => env('URL_SHORTENER_QR_MARGIN', 1),
        'error_correction' => env('URL_SHORTENER_QR_ERROR_CORRECTION', 'M'), // L, M, Q, H
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Protection
    |--------------------------------------------------------------------------
    |
    | Configuration for password protection feature.
    |
    */
    'password' => [
        'enabled' => env('URL_SHORTENER_PASSWORD_ENABLED', true),
        'min_length' => env('URL_SHORTENER_PASSWORD_MIN_LENGTH', 4),
        'max_length' => env('URL_SHORTENER_PASSWORD_MAX_LENGTH', 64),
    ],

    /*
    |--------------------------------------------------------------------------
    | Link Expiry
    |--------------------------------------------------------------------------
    |
    | Configuration for link expiration.
    |
    */
    'expiry' => [
        'enabled' => env('URL_SHORTENER_EXPIRY_ENABLED', true),
        'default_days' => env('URL_SHORTENER_EXPIRY_DAYS', null), // null = no default expiry
    ],

    /*
    |--------------------------------------------------------------------------
    | Click Limits
    |--------------------------------------------------------------------------
    |
    | Configuration for click limits (maximum number of clicks).
    |
    */
    'click_limit' => [
        'enabled' => env('URL_SHORTENER_CLICK_LIMIT_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to use. If null, uses default connection.
    |
    */
    'connection' => env('URL_SHORTENER_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for improved performance.
    |
    */
    'cache' => [
        'enabled' => env('URL_SHORTENER_CACHE_ENABLED', true),
        'prefix' => env('URL_SHORTENER_CACHE_PREFIX', 'url_shortener'),
        'ttl' => env('URL_SHORTENER_CACHE_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Advanced analytics settings.
    |
    */
    'analytics' => [
        'retention_days' => env('URL_SHORTENER_ANALYTICS_RETENTION', 365), // How long to keep analytics data
        'real_time' => env('URL_SHORTENER_ANALYTICS_REAL_TIME', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Domain Support
    |--------------------------------------------------------------------------
    |
    | Allow users to use custom domains for shortened URLs.
    |
    */
    'custom_domain' => [
        'enabled' => env('URL_SHORTENER_CUSTOM_DOMAIN_ENABLED', false),
        'allowed_domains' => env('URL_SHORTENER_ALLOWED_DOMAINS', null), // Comma-separated list
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook settings for notifications on link events.
    |
    */
    'webhook' => [
        'enabled' => env('URL_SHORTENER_WEBHOOK_ENABLED', false),
        'url' => env('URL_SHORTENER_WEBHOOK_URL', null),
        'events' => [
            'link_created',
            'link_clicked',
            'link_expired',
            'link_limit_reached',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | API endpoint settings.
    |
    */
    'api' => [
        'enabled' => env('URL_SHORTENER_API_ENABLED', true),
        'prefix' => env('URL_SHORTENER_API_PREFIX', 'api/url-shortener'),
        'middleware' => ['api'],
        'rate_limit' => env('URL_SHORTENER_API_RATE_LIMIT', 60), // requests per minute
    ],
];
