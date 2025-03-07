<?php

namespace App\Providers;

use App\Contracts\Services\BookmarkServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Services\V1\BookmarkService;
use App\Services\V1\UserService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(BookmarkServiceInterface::class, BookmarkService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
