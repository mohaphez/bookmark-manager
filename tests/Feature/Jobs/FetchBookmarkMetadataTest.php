<?php

namespace Tests\Feature\Jobs;

use App\Enums\MetadataStatus;
use App\Jobs\FetchBookmarkMetadata;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FetchBookmarkMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_when_bookmark_is_created(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $bookmarkData = [
            'url' => 'https://www.example.com',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v1/bookmarks', $bookmarkData);

        $response->assertStatus(201);

        Queue::assertPushedOn('bookmarks', FetchBookmarkMetadata::class);
    }

    public function test_bookmark_metadata_status_is_set_to_pending_on_creation(): void
    {
        $user = User::factory()->create();

        $bookmark = Bookmark::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://www.example.com',
        ]);

        $this->assertEquals(MetadataStatus::PENDING, $bookmark->metadata_status);
    }
}
