<?php

namespace App\Pipes\Bookmark;

use App\Enums\MetadataStatus;
use App\Models\Bookmark;
use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchWebpage
{
    /**
     * Fetch the webpage content
     *
     * @return mixed
     */
    public function handle(Bookmark $bookmark, Closure $next)
    {
        try {

            $response = Http::withOptions([
                'timeout' => 10,
                'connect_timeout' => 5,
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => ['http', 'https'],
                    'track_redirects' => true,
                ],
            ])->get($bookmark->url);

            if (! $response->successful()) {
                throw new \Exception("Failed to fetch webpage: HTTP status {$response->status()}");
            }

            $bookmark->html_content = $response->body();

            $bookmark->update([
                'metadata_status' => MetadataStatus::PROCESSING->value,
            ]);

            return $next($bookmark);

        } catch (\Exception $e) {
            Log::error('Unexpected error fetching webpage', [
                'bookmark_id' => $bookmark->id,
                'url' => $bookmark->url,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Unexpected error fetching webpage: {$e->getMessage()}");
        }
    }
}
