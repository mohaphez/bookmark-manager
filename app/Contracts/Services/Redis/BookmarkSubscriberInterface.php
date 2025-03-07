<?php

namespace App\Contracts\Services\Redis;

interface BookmarkSubscriberInterface
{
    /**
     * Subscribe to the bookmark channel
     */
    public function subscribe(): void;

    /**
     * Process a message from the bookmark channel
     *
     * @param  string  $message  The JSON message from Redis
     */
    public function processMessage(string $message): void;
}
