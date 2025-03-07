<?php

use App\Contracts\Services\BookmarkServiceInterface;
use App\Contracts\Services\Redis\BookmarkPublisherInterface;
use App\Contracts\Services\Redis\BookmarkSubscriberInterface;
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

if (! function_exists('bookmarkSubscriber')) {
    /**
     * Get the BookmarkSubscriber instance
     */
    function bookmarkSubscriber(): BookmarkSubscriberInterface
    {
        return app(BookmarkSubscriberInterface::class);
    }
}

if (! function_exists('bookmarkPublisher')) {
    /**
     * Get the BookmarkPublisher instance
     */
    function bookmarkPublisher(): BookmarkPublisherInterface
    {
        return app(BookmarkPublisherInterface::class);
    }
}
