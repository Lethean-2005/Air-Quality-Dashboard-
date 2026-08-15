<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CacheCountrySvg extends Command
{
    protected $signature = 'svg:cache
        {country?* : Country slug(s) to cache, e.g. cambodia united-states}
        {--all : Download every SVG listed in the remote countrybg directory}
        {--force : Re-download even if already cached locally}';

    protected $description = 'Download country background SVG(s) from myserver.aqi.in and cache them locally under storage/app/public/svg';

    private const REMOTE_BASE = 'https://myserver.aqi.in/countrybg/';
    private const DISK = 'public';
    private const CACHE_DIR = 'svg';

    public function handle(): int
    {
        $disk = Storage::disk(self::DISK);

        // slug => remote href (href stays percent-encoded as served, safe to use directly in the fetch URL)
        $targets = $this->option('all')
            ? $this->fetchRemoteFileList()
            : collect($this->argument('country'))
                ->mapWithKeys(fn ($c) => [strtolower(trim($c)) => rawurlencode(strtolower(trim($c))) . '.svg']);

        if ($targets->isEmpty()) {
            $this->error('No countries to cache. Pass slugs or use --all.');
            return self::FAILURE;
        }

        $targets = $targets->filter(function ($href, $slug) {
            if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
                $this->warn("Skipping invalid slug: {$slug}");
                return false;
            }
            return true;
        });

        if (!$this->option('force')) {
            $targets = $targets->reject(fn ($href, $slug) => $disk->exists(self::CACHE_DIR . "/{$slug}.svg"));
        }

        if ($targets->isEmpty()) {
            $this->info('Nothing to do — all requested SVGs are already cached (use --force to re-download).');
            return self::SUCCESS;
        }

        $this->info("Downloading {$targets->count()} SVG(s)...");
        $failed = [];
        $done = 0;

        foreach ($targets->chunk(20) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => $chunk->map(
                fn ($href, $slug) => $pool->as($slug)->withOptions(['verify' => false])->timeout(30)->get(self::REMOTE_BASE . $href)
            )->all());

            foreach ($responses as $slug => $response) {
                if (!$response instanceof \Illuminate\Http\Client\Response || !$response->successful()) {
                    $this->error("Failed: {$slug}");
                    $failed[] = $slug;
                    continue;
                }

                $disk->put(self::CACHE_DIR . "/{$slug}.svg", $response->body());
                $done++;
                $this->line("Cached: {$slug}.svg ({$done}/{$targets->count()})");
            }
        }

        if ($failed) {
            $this->warn('Failed slugs: ' . implode(', ', $failed));
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function fetchRemoteFileList()
    {
        $response = Http::withoutVerifying()->timeout(30)->get(self::REMOTE_BASE);

        if (!$response->successful()) {
            $this->error('Failed to list remote directory: ' . self::REMOTE_BASE);
            return collect();
        }

        preg_match_all('/href="([^"\/]+\.svg)"/i', $response->body(), $matches);

        return collect($matches[1])->mapWithKeys(function ($href) {
            $slug = strtolower(rawurldecode(pathinfo($href, PATHINFO_FILENAME)));
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-');
            return [$slug => $href];
        });
    }
}
