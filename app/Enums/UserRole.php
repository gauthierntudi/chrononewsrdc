<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Journalist = 'journaliste';
    case Admin = 'admin';
    case SuperAdmin = 'superadmin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Utilisateur',
            self::Journalist => 'Journaliste',
            self::Admin => 'Administrateur',
            self::SuperAdmin => 'Super administrateur',
        };
    }

    public function publishesForFree(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Journalist], true);
    }

    public function autoValidatesArticles(): bool
    {
        return in_array($this, [self::Journalist, self::Admin, self::SuperAdmin], true);
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Journalist], true);
    }

    public function usesAdminPanel(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin], true);
    }

    public function usesUserPanel(): bool
    {
        return in_array($this, [self::User, self::Journalist], true);
    }

    public function adsAreFree(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin], true);
    }

    public function canViewOwnPendingArticles(): bool
    {
        return in_array($this, [self::User, self::Journalist], true);
    }

    public function canViewGlobalPendingQueue(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin], true);
    }

    public function canViewOwnPayments(): bool
    {
        return $this === self::User;
    }

    public function canViewGlobalPayments(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canManageOwnAds(): bool
    {
        return in_array($this, [self::User, self::Journalist, self::Admin, self::SuperAdmin], true);
    }

    public function canManageAllAds(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin], true);
    }

    public function canViewAdRates(): bool
    {
        return in_array($this, [self::User, self::SuperAdmin], true);
    }

    public function canEditAdRates(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canManageHomeVideos(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin], true);
    }

    public function canManageUsers(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canViewSettings(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canManageSettings(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canManageNewsletter(): bool
    {
        return $this === self::SuperAdmin;
    }
}
