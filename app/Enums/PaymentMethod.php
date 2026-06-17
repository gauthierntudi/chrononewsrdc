<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Card = 'carte_bancaire';
    case Mpesa = 'mpesa';
    case Airtel = 'airtel_money';
    case Orange = 'orange_money';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'Carte bancaire',
            self::Mpesa => 'M-Pesa',
            self::Airtel => 'Airtel Money',
            self::Orange => 'Orange Money',
        };
    }

    public function requiresPhone(): bool
    {
        return $this !== self::Card;
    }
}
