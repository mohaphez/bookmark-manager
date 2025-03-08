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
            ])
                ->withUserAgent($this->randomUserAgent())
                ->get($bookmark->url);

            if (! $response->successful()) {
                throw new \Exception("Failed to fetch webpage: HTTP status {$response->status()}");
            }

            $bookmark->update([
                'metadata_status' => MetadataStatus::PROCESSING->value,
            ]);

            $bookmark->html_content = $response->body();

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

    private function randomUserAgent()
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Linux; Android 11; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
