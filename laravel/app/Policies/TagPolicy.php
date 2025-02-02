<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    /**
     * タグ一覧の表示権限
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 特定のタグの表示権限
     */
    public function view(User $user, Tag $tag): bool
    {
        return $user->id === $tag->user_id;
    }

    /**
     * タグの作成権限
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * タグの更新権限
     */
    public function update(User $user, Tag $tag): bool
    {
        return $user->id === $tag->user_id;
    }

    /**
     * タグの削除権限
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $user->id === $tag->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tag $tag): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tag $tag): bool
    {
        return false;
    }
}
