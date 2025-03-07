<?php

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
