<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Services;

use Shammaa\LaravelUrlShortener\Models\ShortLink;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class VisitTracker
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Track a visit
     */
    public function track(ShortLink $link, Request $request): void
    {
        if (!$link->track_visits) {
            return;
        }

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $visitData = [
            'visited_at' => now(),
        ];

        // Track IP
        if ($link->track_ip_address) {
            $visitData['ip_address'] = $request->ip();
        }

        // Track User Agent
        if ($link->track_user_agent) {
            $visitData['user_agent'] = $request->userAgent();
            $visitData['device_type'] = $this->getDeviceType($agent);
            $visitData['device_name'] = $agent->device();
            $visitData['platform'] = $agent->platform();
            $visitData['platform_version'] = $agent->version($agent->platform());
            $visitData['browser'] = $agent->browser();
            $visitData['browser_version'] = $agent->version($agent->browser());
            $visitData['is_bot'] = $agent->isRobot();
            $visitData['is_mobile'] = $agent->isMobile();
            $visitData['is_tablet'] = $agent->isTablet();
            $visitData['language'] = $request->getPreferredLanguage();
        }

        // Track Referer
        if ($link->track_referer) {
            $referer = $request->header('referer');
            if ($referer) {
                $visitData['referer_url'] = $referer;
                $visitData['referer_domain'] = parse_url($referer, PHP_URL_HOST);
            }
        }

        // Track UTM parameters (hidden)
        if ($link->utm_hidden && ($this->config['utm']['hidden'] ?? true)) {
            $visitData['utm_source'] = $request->input('utm_source');
            $visitData['utm_medium'] = $request->input('utm_medium');
            $visitData['utm_campaign'] = $request->input('utm_campaign');
            $visitData['utm_term'] = $request->input('utm_term');
            $visitData['utm_content'] = $request->input('utm_content');
        }

        // Track Geographic location
        if ($link->track_geo && ($this->config['track_geo'] ?? false)) {
            $geoData = $this->getGeoLocation($request->ip());
            if ($geoData) {
                $visitData = array_merge($visitData, $geoData);
            }
        }

        // Track query parameters
        if (!empty($request->query())) {
            $visitData['query_parameters'] = $request->query();
        }

        // Track session
        $visitData['session_id'] = $request->session()->getId();

        // Create visit
        $link->visits()->create($visitData);
    }

    /**
     * Get device type
     */
    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isMobile()) {
            return 'mobile';
        }

        if ($agent->isTablet()) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Get geographic location from IP
     */
    protected function getGeoLocation(?string $ip): ?array
    {
        if (!$ip || $ip === '127.0.0.1' || $ip === '::1') {
            return null;
        }

        // You can integrate with services like MaxMind, IPStack, etc.
        // For now, return null - this should be implemented based on your geolocation service
        
        return null;
    }
}
