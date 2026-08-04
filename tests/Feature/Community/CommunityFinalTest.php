<?php

namespace Tests\Feature\Community;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CommunityFinalTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_module_is_active(): void
    {
        $this->assertDatabaseHas('modules', [
            'slug' => 'community',
            'is_active' => true,
            'route_prefix' => 'community',
        ]);
    }

    public function test_student_dashboard_displays_community_module(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSeeText('Community')
            ->assertSee(route('student.community.index'));
    }

    public function test_student_menu_displays_community_link(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->get(route('student.community.index'))
            ->assertOk()
            ->assertSeeText('Community')
            ->assertSee(route('student.community.index'));
    }

    public function test_student_cannot_moderate_a_post(): void
    {
        $user = $this->verifiedUser();
        $post = CommunityPost::factory()->create();

        $this->actingAs($user)
            ->patch(
                route(
                    'student.community.moderation.posts',
                    $post
                ),
                ['action' => 'hide']
            )
            ->assertForbidden();

        $this->assertSame(
            'published',
            $post->fresh()->status
        );
    }

    public function test_admin_can_hide_and_restore_a_post(): void
    {
        $admin = $this->adminUser();
        $post = CommunityPost::factory()->create();

        $route = route(
            'student.community.moderation.posts',
            $post
        );

        $this->actingAs($admin)
            ->patch(
                $route,
                [
                    'action' => 'hide',
                    'moderation_note' => 'Contenu inapproprié.',
                ]
            )
            ->assertRedirect();

        $post->refresh();

        $this->assertSame('hidden', $post->status);
        $this->assertSame($admin->id, $post->hidden_by);
        $this->assertNotNull($post->hidden_at);

        $this->actingAs($admin)
            ->patch(
                $route,
                ['action' => 'restore']
            )
            ->assertRedirect();

        $post->refresh();

        $this->assertSame('published', $post->status);
        $this->assertNull($post->hidden_by);
        $this->assertNull($post->hidden_at);
    }

    public function test_admin_can_hide_and_restore_a_comment(): void
    {
        $admin = $this->adminUser();
        $comment = CommunityComment::factory()->create();

        $route = route(
            'student.community.moderation.comments',
            $comment
        );

        $this->actingAs($admin)
            ->patch(
                $route,
                ['action' => 'hide']
            )
            ->assertRedirect();

        $this->assertSame(
            'hidden',
            $comment->fresh()->status
        );

        $this->actingAs($admin)
            ->patch(
                $route,
                ['action' => 'restore']
            )
            ->assertRedirect();

        $this->assertSame(
            'published',
            $comment->fresh()->status
        );
    }

    public function test_admin_feed_displays_hidden_content(): void
    {
        $admin = $this->adminUser();

        $post = CommunityPost::factory()
            ->hidden()
            ->create([
                'body' => 'Publication masquée visible par admin.',
            ]);

        $this->actingAs($admin)
            ->get(route('student.community.index'))
            ->assertOk()
            ->assertSeeText($post->body)
            ->assertSeeText('Mode modération administrateur activé');
    }

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function adminUser(): User
    {
        $user = $this->verifiedUser();

        if (method_exists($user, 'roles')) {
            $relation = $user->roles();
            $roleTable = $relation->getRelated()->getTable();
            $columns = Schema::getColumnListing($roleTable);

            $identityColumn = in_array('slug', $columns, true)
                ? 'slug'
                : 'name';

            $roleId = DB::table($roleTable)
                ->where($identityColumn, 'admin')
                ->value('id');

            if ($roleId === null) {
                $attributes = [];

                if (in_array('uuid', $columns, true)) {
                    $attributes['uuid'] = (string) Str::uuid();
                }

                if (in_array('name', $columns, true)) {
                    $attributes['name'] = 'admin';
                }

                if (in_array('display_name', $columns, true)) {
                    $attributes['display_name'] = 'Administrateur';
                }

                if (in_array('slug', $columns, true)) {
                    $attributes['slug'] = 'admin';
                }

                if (in_array('guard_name', $columns, true)) {
                    $attributes['guard_name'] = 'web';
                }

                if (in_array('description', $columns, true)) {
                    $attributes['description'] = 'Administrateur';
                }

                if (in_array('is_active', $columns, true)) {
                    $attributes['is_active'] = true;
                }

                if (in_array('is_enabled', $columns, true)) {
                    $attributes['is_enabled'] = true;
                }

                if (in_array('created_at', $columns, true)) {
                    $attributes['created_at'] = now();
                }

                if (in_array('updated_at', $columns, true)) {
                    $attributes['updated_at'] = now();
                }

                $roleId = DB::table($roleTable)
                    ->insertGetId($attributes);
            }

            $relation->syncWithoutDetaching([$roleId]);

            $user = $user->fresh();

            if ($user->hasRole('admin')) {
                return $user;
            }
        }

        if (Schema::hasColumn('users', 'role')) {
            $user->forceFill([
                'role' => 'admin',
            ])->save();

            return $user->fresh();
        }

        throw new RuntimeException(
            'Impossible de créer un utilisateur admin dans les tests.'
        );
    }
}
