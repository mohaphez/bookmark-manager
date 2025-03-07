<?php

namespace App\Services\V1\Redis;

use App\Contracts\Services\Redis\BookmarkPublisherInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BookmarkPublisher implements BookmarkPublisherInterface
{
    /**
     * The Redis channel to publish to
     */
    private const BOOKMARK_CHANNEL = 'bookmarks:new';

    /**
     * Publish a bookmark to the Redis channel
     *
     * @param  array  $data  Bookmark data with at least 'url' and 'user_id'
     * @return bool Whether the publish was successful
     */
    public function publish(array $data): bool
    {
        try {
            if (! $this->validateData($data)) {
                Log::error('Invalid bookmark data for publishing', ['data' => $data]);

                return false;
            }

            $message = json_encode($data);
            $result = Redis::publish(self::BOOKMARK_CHANNEL, $message);

            Log::info('Published bookmark to Redis', [
                'channel' => self::BOOKMARK_CHANNEL,
                'url' => $data['url'],
                'user_id' => $data['user_id'],
                'result' => $result,
            ]);

            return $result > 0;
        } catch (\Exception $e) {
            Log::error('Error publishing bookmark to Redis', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate the bookmark data
     */
    private function validateData(array $data): bool
    {
        if (! isset($data['url']) || ! isset($data['user_id'])) {
            return false;
        }

        if (! is_string($data['url']) || empty($data['url'])) {
            return false;
        }

        if (! is_numeric($data['user_id']) && ! is_string($data['user_id'])) {
            return false;
        }

        return true;
    }
}
