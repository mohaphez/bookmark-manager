<?php

namespace App\Providers;

use App\Contracts\Services\Redis\BookmarkPublisherInterface;
use App\Contracts\Services\Redis\BookmarkSubscriberInterface;
use App\Services\V1\Redis\BookmarkPublisher;
use App\Services\V1\Redis\BookmarkSubscriber;
use Illuminate\Support\ServiceProvider;

class RedisServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BookmarkSubscriberInterface::class, BookmarkSubscriber::class);
        $this->app->bind(BookmarkPublisherInterface::class, BookmarkPublisher::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
