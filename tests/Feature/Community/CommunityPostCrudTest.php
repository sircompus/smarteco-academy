<?php

namespace Tests\Feature\Community;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPostCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_view_the_community_feed(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $post = CommunityPost::factory()->create([
            'body' => 'Publication visible dans le fil.',
        ]);

        $this->actingAs($user)
            ->get(route('student.community.index'))
            ->assertOk()
            ->assertSeeText('Fil de la communauté')
            ->assertSeeText($post->body);
    }

    public function test_verified_user_can_create_a_post(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('student.community.posts.store'),
                [
                    'body' => 'Ma première publication Community.',
                ]
            );

        $response->assertRedirect(
            route('student.community.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('community_posts', [
            'user_id' => $user->id,
            'body' => 'Ma première publication Community.',
            'status' => 'published',
        ]);
    }

    public function test_hidden_post_is_not_displayed_in_the_feed(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $hiddenPost = CommunityPost::factory()
            ->hidden()
            ->create([
                'body' => 'Publication masquée par la modération.',
            ]);

        $this->actingAs($user)
            ->get(route('student.community.index'))
            ->assertOk()
            ->assertDontSeeText($hiddenPost->body);
    }

    public function test_owner_can_update_their_post(): void
    {
        $owner = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $post = CommunityPost::factory()
            ->for($owner, 'author')
            ->create([
                'body' => 'Ancien contenu',
            ]);

        $this->actingAs($owner)
            ->patch(
                route(
                    'student.community.posts.update',
                    $post
                ),
                [
                    'body' => 'Nouveau contenu',
                ]
            )
            ->assertRedirect(
                route('student.community.index')
            );

        $this->assertDatabaseHas('community_posts', [
            'id' => $post->id,
            'body' => 'Nouveau contenu',
        ]);
    }

    public function test_another_user_cannot_update_or_delete_a_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $post = CommunityPost::factory()
            ->for($owner, 'author')
            ->create();

        $this->actingAs($otherUser)
            ->patch(
                route(
                    'student.community.posts.update',
                    $post
                ),
                [
                    'body' => 'Modification interdite',
                ]
            )
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(
                route(
                    'student.community.posts.destroy',
                    $post
                )
            )
            ->assertForbidden();

        $this->assertDatabaseHas('community_posts', [
            'id' => $post->id,
        ]);
    }

    public function test_owner_can_soft_delete_their_post(): void
    {
        $owner = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $post = CommunityPost::factory()
            ->for($owner, 'author')
            ->create();

        $this->actingAs($owner)
            ->delete(
                route(
                    'student.community.posts.destroy',
                    $post
                )
            )
            ->assertRedirect(
                route('student.community.index')
            );

        $this->assertSoftDeleted('community_posts', [
            'id' => $post->id,
        ]);
    }

    public function test_post_body_is_required(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->from(route('student.community.index'))
            ->post(
                route('student.community.posts.store'),
                [
                    'body' => '',
                ]
            )
            ->assertRedirect(
                route('student.community.index')
            )
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('community_posts', 0);
    }
}
