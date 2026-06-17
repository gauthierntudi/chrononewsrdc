<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class SubscriptionPlan extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'duration_days',
        'price',
        'description',
        'is_active',
    ];

    public function usesTimestamps(): bool
    {
        return Schema::hasColumn($this->getTable(), 'created_at');
    }

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }
}
