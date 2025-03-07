<?php

namespace App\Services\V1;

use App\Contracts\Services\BookmarkServiceInterface;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BookmarkService implements BookmarkServiceInterface
{
    /**
     * Create a new bookmark for a user
     */
    public function createBookmark(User $user, array $data): Bookmark
    {
        return $user->bookmarks()->create([
            'url' => $data['url'],
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Get all bookmarks for a user
     */
    public function getUserBookmarks(User $user): Collection
    {
        return $user->bookmarks()->latest()->get();
    }
}
