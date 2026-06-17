<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $article->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Article $article): bool
    {
        return $user->isSuperAdmin() || $article->user_id === $user->id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->isSuperAdmin() || $article->user_id === $user->id;
    }

    public function validate(User $user, Article $article): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }
}
