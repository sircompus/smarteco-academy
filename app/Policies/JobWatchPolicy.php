<?php

namespace App\Policies;

use App\Models\JobWatch;
use App\Models\User;

class JobWatchPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobWatch $jobWatch): bool
    {
        return $user->id === $jobWatch->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, JobWatch $jobWatch): bool
    {
        return $user->id === $jobWatch->user_id;
    }

    public function delete(User $user, JobWatch $jobWatch): bool
    {
        return $user->id === $jobWatch->user_id;
    }

    public function restore(User $user, JobWatch $jobWatch): bool
    {
        return $user->id === $jobWatch->user_id;
    }

    public function forceDelete(User $user, JobWatch $jobWatch): bool
    {
        return $user->id === $jobWatch->user_id;
    }
}