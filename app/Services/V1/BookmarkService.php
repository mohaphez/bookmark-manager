<?php

namespace App\Services\V1;

use App\Contracts\Services\BookmarkServiceInterface;
use App\Enums\MetadataStatus;
use App\Jobs\FetchBookmarkMetadata;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class BookmarkService implements BookmarkServiceInterface
{

    public function createBookmark(User $user, array $data): Bookmark
    {
        $bookmark = $user->bookmarks()->create([
            'url' => $data['url'],
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata_status' => MetadataStatus::PENDING->value,
        ]);

        FetchBookmarkMetadata::dispatch($bookmark)->onQueue('bookmarks');

        return $bookmark;
    }

    public function getUserBookmarks(User $user): Collection
    {
        return $user->bookmarks()->latest()->get();
    }

    public function deleteBookmark(User $user, string $bookmarkId): bool
    {
        $bookmark = $user->bookmarks()->where('id', $bookmarkId)->first();

        if (! $bookmark) {
            return false;
        }

        return $bookmark->delete();
    }

    public function updateBookmarkMetadata(Bookmark $bookmark): bool
    {
        try {

            FetchBookmarkMetadata::dispatch($bookmark)->onQueue('bookmarks');

            $bookmark->update([
                'metadata_status' => MetadataStatus::PENDING->value,
                'metadata_error' => null,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to dispatch bookmark metadata update job', [
                'bookmark_id' => $bookmark->id,
                'url' => $bookmark->url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
