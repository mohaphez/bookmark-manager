<?php

use App\Contracts\Services\BookmarkServiceInterface;
use App\Contracts\Services\UserServiceInterface;

if (! function_exists('userService')) {
    /**
     * Get the UserService instance
     */
    function userService(): UserServiceInterface
    {
        return app(UserServiceInterface::class);
    }
}

if (! function_exists('bookmarkService')) {
    /**
     * Get the BookmarkService instance
     */
    function bookmarkService(): BookmarkServiceInterface
    {
        return app(BookmarkServiceInterface::class);
    }
}
