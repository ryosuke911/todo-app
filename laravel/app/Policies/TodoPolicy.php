<?php

namespace App\Policies;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TodoPolicy
{
    use HandlesAuthorization;

    /**
     * ユーザーがToDoを更新できるかどうかを判定
     */
    public function update(User $user, Todo $todo)
    {
        return $user->id === $todo->user_id;
    }

    /**
     * ユーザーがToDoを削除できるかどうかを判定
     */
    public function delete(User $user, Todo $todo)
    {
        return $user->id === $todo->user_id;
    }

    public function view(User $user, Todo $todo)
    {
        return $user->id === $todo->user_id;
    }
} 