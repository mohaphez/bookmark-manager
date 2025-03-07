<?php

namespace App\Services\V1\Redis;

use App\Contracts\Services\Redis\BookmarkSubscriberInterface;
use App\Enums\MetadataStatus;
use App\Jobs\FetchBookmarkMetadata;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BookmarkSubscriber implements BookmarkSubscriberInterface
{
    /**
     * The Redis channel to subscribe to
     */
    private const BOOKMARK_CHANNEL = 'bookmarks:new';

    /**
     * Subscribe to the bookmark channel
     */
    public function subscribe(): void
    {
        Log::info('Starting bookmark subscriber on channel: '.self::BOOKMARK_CHANNEL);

        Redis::subscribe([self::BOOKMARK_CHANNEL], function ($message) {
            $this->processMessage($message);
        });
    }

    /**
     * Process a message from the bookmark channel
     */
    public function processMessage(string $message): void
    {
        try {
            Log::info('Received bookmark message', ['message' => $message]);

            $data = json_decode($message, true);

            if (! $this->validateMessage($data)) {
                Log::error('Invalid bookmark message format', ['message' => $message]);

                return;
            }

            $bookmark = $this->createOrUpdateBookmark($data);

            if ($bookmark) {
                FetchBookmarkMetadata::dispatch($bookmark)->onQueue('bookmarks');

                Log::info('Bookmark metadata fetch job dispatched', [
                    'bookmark_id' => $bookmark->id,
                    'url' => $bookmark->url,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing bookmark message', [
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Validate the message format
     */
    private function validateMessage(?array $data): bool
    {
        if (! $data) {
            return false;
        }

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

    /**
     * Create or update a bookmark from the message data
     */
    private function createOrUpdateBookmark(array $data): ?Bookmark
    {

        $user = User::find($data['user_id']);

        if (! $user) {
            Log::error('User not found for bookmark', ['user_id' => $data['user_id']]);

            return null;
        }

        $bookmark = $user->bookmarks()
            ->where('url', $data['url'])
            ->first();

        if ($bookmark) {
            if (! $bookmark->hasFailedMetadata()) {
                Log::info('Bookmark already exists and is not in failed state', [
                    'bookmark_id' => $bookmark->id,
                    'url' => $bookmark->url,
                    'status' => $bookmark->metadata_status->value,
                ]);

                return null;
            }

            $bookmark->update([
                'metadata_status' => MetadataStatus::PENDING->value,
                'metadata_error' => null,
            ]);

            Log::info('Bookmark status reset to pending', [
                'bookmark_id' => $bookmark->id,
                'url' => $bookmark->url,
            ]);

            return $bookmark;
        }

        $bookmark = $user->bookmarks()->create([
            'url' => $data['url'],
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata_status' => MetadataStatus::PENDING->value,
        ]);

        Log::info('New bookmark created from message', [
            'bookmark_id' => $bookmark->id,
            'url' => $bookmark->url,
        ]);

        return $bookmark;
    }
}
