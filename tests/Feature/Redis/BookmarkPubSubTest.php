<?php

namespace Tests\Feature\Redis;

use App\Contracts\Services\Redis\BookmarkPublisherInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class BookmarkPubSubTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_publish_bookmark_to_redis(): void
    {
        Redis::shouldReceive('publish')
            ->once()
            ->with('bookmarks:new', json_encode([
                'url' => 'https://example.com',
                'user_id' => '1',
            ]))
            ->andReturn(1);

        $publisher = $this->app->make(BookmarkPublisherInterface::class);

        $result = $publisher->publish([
            'url' => 'https://example.com',
            'user_id' => '1'
        ]);

        $this->assertTrue($result);
    }

    public function test_bookmark_controller_publishes_to_redis(): void
    {

        $user = User::factory()->create();

        Redis::shouldReceive('publish')
            ->once()
            ->andReturn(1);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/bookmarks', [
                'url' => 'https://example.com',
            ]);

        $response->assertStatus(202)
            ->assertJson([
                'status' => 'success',
                'message' => 'Bookmark queued for processing',
                'data' => [
                    'url' => 'https://example.com',
                    'processing' => true,
                ],
            ]);
    }

    public function test_bookmark_controller_falls_back_to_direct_processing(): void
    {

        $user = User::factory()->create();

        Redis::shouldReceive('publish')
            ->once()
            ->andReturn(0);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/bookmarks', [
                'url' => 'https://example.com',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Bookmark created successfully',
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'url' => 'https://example.com',
        ]);
    }
}
