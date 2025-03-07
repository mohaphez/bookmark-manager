<?php

namespace App\Contracts\Services;

use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface BookmarkServiceInterface
{
    /**
     * Create a new bookmark for a user
     */
    public function createBookmark(User $user, array $data): Bookmark;

    /**
     * Get all bookmarks for a user
     */
    public function getUserBookmarks(User $user): Collection;

    /**
     * Delete a bookmark
     */
    public function deleteBookmark(User $user, string $bookmarkId): bool;
}
