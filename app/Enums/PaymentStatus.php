<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    /** @deprecated Legacy alias */
    public static function fromLegacy(string $value): self
    {
        return match ($value) {
            'en_attente' => self::Pending,
            'reussi' => self::Succeeded,
            'echoue' => self::Failed,
            default => self::tryFrom($value) ?? self::Pending,
        };
    }
}
