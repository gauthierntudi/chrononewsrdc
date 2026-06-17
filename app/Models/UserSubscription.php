<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class UserSubscription extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return 'user_subscriptions';
    }

    public function usesTimestamps(): bool
    {
        return Schema::hasColumn($this->getTable(), 'updated_at');
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function getStartsAtAttribute(): ?Carbon
    {
        $value = $this->attributes['starts_at'] ?? $this->attributes['start_date'] ?? null;

        return $value ? $this->asDateTime($value) : null;
    }

    public function getEndsAtAttribute(): ?Carbon
    {
        $value = $this->attributes['ends_at'] ?? $this->attributes['end_date'] ?? null;

        return $value ? $this->asDateTime($value) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        $foreignKey = Schema::hasColumn($this->getTable(), 'subscription_plan_id')
            ? 'subscription_plan_id'
            : 'plan_id';

        return $this->belongsTo(SubscriptionPlan::class, $foreignKey);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $endsAt = $this->ends_at;

        return $endsAt !== null && $endsAt->isFuture();
    }
}
