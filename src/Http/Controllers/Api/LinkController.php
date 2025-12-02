<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Shammaa\LaravelUrlShortener\Models\ShortLink;
use Shammaa\LaravelUrlShortener\Services\LinkManager;
use Illuminate\Support\Facades\Validator;

class LinkController extends Controller
{
    protected LinkManager $linkManager;

    public function __construct(LinkManager $linkManager)
    {
        $this->linkManager = $linkManager;
    }

    /**
     * Display a listing of the resource
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShortLink::query();

        // Filter by user if authenticated
        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $links = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json($links);
    }

    /**
     * Store a newly created resource
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'destination_url' => 'required|url|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'key' => 'nullable|string|alpha_num|max:50|unique:short_links,key',
            'password' => 'nullable|string|min:4|max:64',
            'expires_in_days' => 'nullable|integer|min:1|max:3650',
            'click_limit' => 'nullable|integer|min:1',
            'track_visits' => 'nullable|boolean',
            'utm_parameters' => 'nullable|array',
            'tags' => 'nullable|array',
            'group' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attributes = $validator->validated();
        
        // Set user if authenticated
        if ($request->user()) {
            $attributes['user_id'] = $request->user()->id;
            $attributes['user_type'] = get_class($request->user());
        }

        $link = $this->linkManager->create($attributes);

        return response()->json([
            'data' => $link->load('visits'),
            'short_url' => $this->linkManager->getShortUrl($link),
        ], 201);
    }

    /**
     * Display the specified resource
     */
    public function show(string $key): JsonResponse
    {
        $link = $this->linkManager->findByKey($key);

        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        return response()->json([
            'data' => $link->load(['visits', 'analytics']),
            'short_url' => $this->linkManager->getShortUrl($link),
        ]);
    }

    /**
     * Update the specified resource
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $link = $this->linkManager->findByKey($key);

        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'destination_url' => 'sometimes|required|url|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'password' => 'nullable|string|min:4|max:64',
            'is_active' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
            'click_limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attributes = $validator->validated();

        // Handle password update
        if (isset($attributes['password'])) {
            $attributes['password'] = \Hash::make($attributes['password']);
            $attributes['password_protected'] = !empty($attributes['password']);
        }

        $link->update($attributes);

        return response()->json([
            'data' => $link->fresh(),
            'short_url' => $this->linkManager->getShortUrl($link),
        ]);
    }

    /**
     * Remove the specified resource
     */
    public function destroy(string $key): JsonResponse
    {
        $link = $this->linkManager->findByKey($key);

        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        $link->delete();

        return response()->json(['message' => 'Link deleted successfully'], 200);
    }

    /**
     * Get analytics for a link
     */
    public function analytics(string $key): JsonResponse
    {
        $link = $this->linkManager->findByKey($key);

        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        $analytics = [
            'total_clicks' => $link->clicks_count,
            'unique_clicks' => $link->visits()->distinct('ip_address')->count(),
            'clicks_today' => $link->visits()->today()->count(),
            'clicks_this_week' => $link->visits()->thisWeek()->count(),
            'clicks_this_month' => $link->visits()->thisMonth()->count(),
            'top_countries' => $link->visits()
                ->selectRaw('country, country_code, COUNT(*) as clicks')
                ->whereNotNull('country')
                ->groupBy('country', 'country_code')
                ->orderByDesc('clicks')
                ->limit(10)
                ->get(),
            'top_browsers' => $link->visits()
                ->selectRaw('browser, COUNT(*) as clicks')
                ->whereNotNull('browser')
                ->groupBy('browser')
                ->orderByDesc('clicks')
                ->limit(10)
                ->get(),
            'top_platforms' => $link->visits()
                ->selectRaw('platform, COUNT(*) as clicks')
                ->whereNotNull('platform')
                ->groupBy('platform')
                ->orderByDesc('clicks')
                ->limit(10)
                ->get(),
            'device_types' => $link->visits()
                ->selectRaw('device_type, COUNT(*) as clicks')
                ->whereNotNull('device_type')
                ->groupBy('device_type')
                ->orderByDesc('clicks')
                ->get(),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get QR code for a link
     */
    public function qrCode(string $key): JsonResponse
    {
        $link = $this->linkManager->findByKey($key);

        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        $qrCodeUrl = $this->linkManager->generateQrCode($link);

        if (!$qrCodeUrl) {
            return response()->json(['message' => 'QR code generation failed'], 500);
        }

        return response()->json([
            'data' => [
                'qr_code_url' => $qrCodeUrl,
                'short_url' => $this->linkManager->getShortUrl($link),
            ],
        ]);
    }
}
