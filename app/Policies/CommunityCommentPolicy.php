<?php

namespace App\Policies;

use App\Models\CommunityComment;
use App\Models\User;

class CommunityCommentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(
        User $user,
        CommunityComment $communityComment
    ): bool {
        return (int) $communityComment->user_id === (int) $user->id;
    }

    public function delete(
        User $user,
        CommunityComment $communityComment
    ): bool {
        return (int) $communityComment->user_id === (int) $user->id;
    }
}
