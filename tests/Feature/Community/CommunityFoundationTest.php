<?php

namespace Tests\Feature\Community;

use App\Models\CommunityComment;
use App\Models\CommunityLike;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CommunityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_has_an_author_and_generates_a_uuid(): void
    {
        $user = User::factory()->create();

        $post = CommunityPost::factory()
            ->for($user, 'author')
            ->create();

        $this->assertTrue($post->author->is($user));
        $this->assertNotEmpty($post->uuid);
        $this->assertSame('published', $post->status);
    }

    public function test_post_relations_are_connected(): void
    {
        $post = CommunityPost::factory()->create();

        $comment = CommunityComment::factory()
            ->for($post, 'post')
            ->create();

        $like = CommunityLike::factory()
            ->for($post, 'post')
            ->create();

        $this->assertTrue(
            $post->comments()->whereKey($comment)->exists()
        );

        $this->assertTrue(
            $post->likes()->whereKey($like)->exists()
        );
    }

    public function test_user_can_like_a_post_only_once(): void
    {
        $post = CommunityPost::factory()->create();
        $user = User::factory()->create();

        CommunityLike::factory()
            ->for($post, 'post')
            ->for($user)
            ->create();

        $this->expectException(QueryException::class);

        CommunityLike::factory()
            ->for($post, 'post')
            ->for($user)
            ->create();
    }

    public function test_force_deleting_a_post_cascades_relations(): void
    {
        $post = CommunityPost::factory()->create();

        $comment = CommunityComment::factory()
            ->for($post, 'post')
            ->create();

        $like = CommunityLike::factory()
            ->for($post, 'post')
            ->create();

        $post->forceDelete();

        $this->assertDatabaseMissing('community_comments', [
            'id' => $comment->id,
        ]);

        $this->assertDatabaseMissing('community_likes', [
            'id' => $like->id,
        ]);
    }

    public function test_only_owner_can_update_or_delete_a_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $post = CommunityPost::factory()
            ->for($owner, 'author')
            ->create();

        $this->assertTrue(
            Gate::forUser($owner)->allows('update', $post)
        );

        $this->assertTrue(
            Gate::forUser($owner)->allows('delete', $post)
        );

        $this->assertFalse(
            Gate::forUser($otherUser)->allows('update', $post)
        );

        $this->assertFalse(
            Gate::forUser($otherUser)->allows('delete', $post)
        );
    }
}
