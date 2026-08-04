<?php

namespace App\Policies;

use App\Models\CommunityPost;
use App\Models\User;

class CommunityPostPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(
        User $user,
        CommunityPost $communityPost
    ): bool {
        return $communityPost->status === 'published'
            || (int) $communityPost->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(
        User $user,
        CommunityPost $communityPost
    ): bool {
        return (int) $communityPost->user_id === (int) $user->id;
    }

    public function delete(
        User $user,
        CommunityPost $communityPost
    ): bool {
        return (int) $communityPost->user_id === (int) $user->id;
    }
}
