<?php

namespace App\Enums;

enum ValidationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public static function fromLegacy(string $value): self
    {
        return match ($value) {
            'en_attente' => self::Pending,
            'valide' => self::Approved,
            'rejete' => self::Rejected,
            default => self::tryFrom($value) ?? self::Pending,
        };
    }
}
