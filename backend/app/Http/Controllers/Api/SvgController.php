<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SvgController extends Controller
{
    private const DISK = 'public';
    private const CACHE_DIR = 'svg';

    /**
     * Serve a country background SVG from the local cache only.
     * Populate the cache ahead of time via `php artisan svg:cache {country}`.
     */
    public function show(string $country)
    {
        $slug = strtolower(trim($country));

        if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid country slug'], 400);
        }

        $disk = Storage::disk(self::DISK);
        $path = self::CACHE_DIR . "/{$slug}.svg";

        if (!$disk->exists($path)) {
            return response()->json([
                'status' => 'error',
                'message' => "SVG not cached for '{$slug}'. Run: php artisan svg:cache {$slug}",
            ], 404);
        }

        return response($disk->get($path), 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=2592000, immutable');
    }

    /**
     * List the country slugs currently available in the local cache.
     */
    public function index()
    {
        $disk = Storage::disk(self::DISK);

        $slugs = collect($disk->files(self::CACHE_DIR))
            ->map(fn ($path) => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values();

        return response()->json(['status' => 'ok', 'data' => $slugs]);
    }
}
