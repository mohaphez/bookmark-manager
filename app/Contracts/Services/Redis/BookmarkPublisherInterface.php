<?php

namespace App\Contracts\Services\Redis;

interface BookmarkPublisherInterface
{
    /**
     * Publish a bookmark to the Redis channel
     *
     * @param  array  $data  Bookmark data with at least 'url' and 'user_id'
     * @return bool Whether the publish was successful
     */
    public function publish(array $data): bool;
}
