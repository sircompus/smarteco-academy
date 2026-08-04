<?php

namespace Tests\Feature\Community;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_comment_on_a_post(): void
    {
        $user = $this->verifiedUser();
        $post = CommunityPost::factory()->create();

        $this->actingAs($user)
            ->post(
                route(
                    'student.community.comments.store',
                    $post
                ),
                [
                    'body' => 'Commentaire utile.',
                ]
            )
            ->assertRedirect();

        $this->assertDatabaseHas('community_comments', [
            'community_post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Commentaire utile.',
            'status' => 'published',
        ]);
    }

    public function test_comment_body_is_required(): void
    {
        $user = $this->verifiedUser();
        $post = CommunityPost::factory()->create();

        $this->actingAs($user)
            ->from(route('student.community.index'))
            ->post(
                route(
                    'student.community.comments.store',
                    $post
                ),
                [
                    'body' => '',
                ]
            )
            ->assertRedirect(
                route('student.community.index')
            )
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('community_comments', 0);
    }

    public function test_comment_is_displayed_in_the_feed(): void
    {
        $user = $this->verifiedUser();

        $comment = CommunityComment::factory()->create([
            'body' => 'Ce commentaire doit être visible.',
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->get(route('student.community.index'))
            ->assertOk()
            ->assertSeeText($comment->body);
    }

    public function test_hidden_comment_is_not_displayed(): void
    {
        $user = $this->verifiedUser();

        $comment = CommunityComment::factory()
            ->hidden()
            ->create([
                'body' => 'Commentaire masqué.',
            ]);

        $this->actingAs($user)
            ->get(route('student.community.index'))
            ->assertOk()
            ->assertDontSeeText($comment->body);
    }

    public function test_owner_can_delete_their_comment(): void
    {
        $owner = $this->verifiedUser();

        $comment = CommunityComment::factory()
            ->for($owner, 'author')
            ->create();

        $this->actingAs($owner)
            ->delete(
                route(
                    'student.community.comments.destroy',
                    [$comment->post, $comment]
                )
            )
            ->assertRedirect();

        $this->assertSoftDeleted('community_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_another_user_cannot_delete_a_comment(): void
    {
        $otherUser = $this->verifiedUser();

        $comment = CommunityComment::factory()->create();

        $this->actingAs($otherUser)
            ->delete(
                route(
                    'student.community.comments.destroy',
                    [$comment->post, $comment]
                )
            )
            ->assertForbidden();

        $this->assertDatabaseHas('community_comments', [
            'id' => $comment->id,
            'deleted_at' => null,
        ]);
    }

    public function test_comment_must_belong_to_the_selected_post(): void
    {
        $owner = $this->verifiedUser();

        $post = CommunityPost::factory()->create();

        $comment = CommunityComment::factory()
            ->for($owner, 'author')
            ->create();

        $this->actingAs($owner)
            ->delete(
                route(
                    'student.community.comments.destroy',
                    [$post, $comment]
                )
            )
            ->assertNotFound();
    }

    public function test_user_can_like_and_unlike_a_post(): void
    {
        $user = $this->verifiedUser();
        $post = CommunityPost::factory()->create();

        $route = route(
            'student.community.likes.toggle',
            $post
        );

        $this->actingAs($user)
            ->post($route)
            ->assertRedirect();

        $this->assertDatabaseHas('community_likes', [
            'community_post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post($route)
            ->assertRedirect();

        $this->assertDatabaseMissing('community_likes', [
            'community_post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_hidden_post_cannot_receive_comments_or_likes(): void
    {
        $user = $this->verifiedUser();

        $post = CommunityPost::factory()
            ->hidden()
            ->create();

        $this->actingAs($user)
            ->post(
                route(
                    'student.community.comments.store',
                    $post
                ),
                [
                    'body' => 'Interdit',
                ]
            )
            ->assertNotFound();

        $this->actingAs($user)
            ->post(
                route(
                    'student.community.likes.toggle',
                    $post
                )
            )
            ->assertNotFound();

        $this->assertDatabaseCount('community_comments', 0);
        $this->assertDatabaseCount('community_likes', 0);
    }

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }
}
