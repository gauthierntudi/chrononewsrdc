<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

final class DashboardAccess
{
    public static function for(User $user): array
    {
        $role = $user->role ?? UserRole::User;

        return [
            'panel' => $role->usesAdminPanel() ? 'admin' : 'user',
            'role' => $role->value,
            'stats' => true,
            'pendingOwn' => $role->canViewOwnPendingArticles(),
            'pendingGlobal' => $role->canViewGlobalPendingQueue(),
            'articles' => true,
            'createArticle' => true,
            'ownPayments' => $role->canViewOwnPayments(),
            'subscriptions' => true,
            'globalPayments' => $role->canViewGlobalPayments(),
            'ownAds' => $role->canManageOwnAds(),
            'globalAds' => $role->canManageAllAds(),
            'adsFree' => $role->adsAreFree(),
            'adRatesView' => $role->canViewAdRates(),
            'adRatesEdit' => $role->canEditAdRates(),
            'homeVideos' => $role->canManageHomeVideos(),
            'users' => $role->canManageUsers(),
            'settings' => $role->canViewSettings(),
            'settingsEdit' => $role->canManageSettings(),
            'newsletter' => $role->canManageNewsletter(),
            'publishWithoutValidation' => $role->autoValidatesArticles(),
            'publishForFree' => $role->publishesForFree(),
        ];
    }
}
