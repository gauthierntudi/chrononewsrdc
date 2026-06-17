<?php

namespace App\Enums;

enum ArticlePaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Free = 'free';

    public static function fromLegacy(string $value): self
    {
        return match ($value) {
            'en_attente' => self::Pending,
            'paye' => self::Paid,
            'gratuit' => self::Free,
            default => self::tryFrom($value) ?? self::Pending,
        };
    }
}
