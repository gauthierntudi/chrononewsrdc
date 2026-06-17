<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvertisementRate extends Model
{
    protected $fillable = [
        'format',
        'label',
        'dimensions',
        'price_7_days',
        'price_15_days',
        'price_30_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_7_days' => 'decimal:2',
            'price_15_days' => 'decimal:2',
            'price_30_days' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function priceForDays(int $days): ?float
    {
        return match ($days) {
            7 => (float) $this->price_7_days,
            15 => (float) $this->price_15_days,
            30 => (float) $this->price_30_days,
            default => null,
        };
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'format', 'format');
    }
}
