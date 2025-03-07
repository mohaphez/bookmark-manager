<?php

namespace Tests\Feature\Api\V1\Bookmark;

use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class BookmarkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_retrieve_bookmarks(): void
    {

        $user = User::factory()->create();

        Bookmark::factory(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/bookmarks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'url',
                        'title',
                        'description',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Bookmarks retrieved successfully',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_unauthenticated_user_cannot_retrieve_bookmarks(): void
    {
        $response = $this->getJson('/api/v1/bookmarks');

        $response->assertStatus(401);
    }

    public function test_user_can_only_see_their_own_bookmarks(): void
    {

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Bookmark::factory(2)->create([
            'user_id' => $user1->id,
        ]);

        Bookmark::factory(3)->create([
            'user_id' => $user2->id,
        ]);

        $response = $this->actingAs($user1)
            ->getJson('/api/v1/bookmarks');

        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_can_create_bookmark(): void
    {

        Redis::shouldReceive('publish')
            ->once()
            ->andReturn(1);

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $bookmarkData = [
            'url' => 'https://example.com',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bookmarks', $bookmarkData);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'url',
                    'processing',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Bookmark queued for processing',
                'data' => [
                    'url' => $bookmarkData['url'],
                    'processing' => true,
                ],
            ]);
    }

    public function test_user_can_create_bookmark_with_direct_processing(): void
    {

        Redis::shouldReceive('publish')
            ->once()
            ->andReturn(0);

        $user = User::factory()->create();

        $bookmarkData = [
            'url' => 'https://example.com',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v1/bookmarks', $bookmarkData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'url',
                    'title',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Bookmark created successfully',
                'data' => [
                    'url' => $bookmarkData['url'],
                ],
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'url' => $bookmarkData['url'],
        ]);
    }

    public function test_unauthenticated_user_cannot_create_bookmark(): void
    {
        $bookmarkData = [
            'url' => 'https://www.example.com',
            'title' => 'Example Website',
            'description' => 'This is an example website',
        ];

        $response = $this->postJson('/api/v1/bookmarks', $bookmarkData);

        $response->assertStatus(401);
    }

    public function test_bookmark_creation_validation_errors(): void
    {

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/bookmarks', [
                'title' => 'Example Website',
                'description' => 'This is an example website',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/bookmarks', [
                'url' => 'not-a-url',
                'title' => 'Example Website',
                'description' => 'This is an example website',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_user_cannot_create_duplicate_bookmark(): void
    {

        $user = User::factory()->create();

        Bookmark::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://www.example.com',
        ]);

        $bookmarkData = [
            'url' => 'https://www.example.com',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v1/bookmarks', $bookmarkData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /**
     * Test that a user can delete their own bookmark
     */
    public function test_user_can_delete_bookmark(): void
    {
        $user = User::factory()->create();

        $bookmark = Bookmark::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Bookmark deleted successfully',
                'data' => null,
            ]);

        $this->assertSoftDeleted('bookmarks', [
            'id' => $bookmark->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_bookmark(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $bookmark = Bookmark::factory()->create([
            'user_id' => $user2->id,
        ]);

        $response = $this->actingAs($user1)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Bookmark not found or you do not have permission to delete it',
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'id' => $bookmark->id,
            'deleted_at' => null,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_bookmark(): void
    {
        $user = User::factory()->create();
        $bookmark = Bookmark::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/v1/bookmarks/{$bookmark->id}");
        $response->assertStatus(401);

        $this->assertDatabaseHas('bookmarks', [
            'id' => $bookmark->id,
            'deleted_at' => null,
        ]);
    }
}
