<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Payment extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return Schema::hasTable('paiements') ? 'paiements' : 'payments';
    }

    public function usesLegacySchema(): bool
    {
        return $this->getTable() === 'paiements';
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
        ];
    }

    public function getAmountAttribute(): ?float
    {
        $value = $this->attributes['amount'] ?? $this->attributes['montant'] ?? null;

        return $value !== null ? (float) $value : null;
    }

    public function getStatusAttribute(): ?PaymentStatus
    {
        $raw = $this->attributes['status'] ?? $this->attributes['statut'] ?? null;

        if ($raw === null) {
            return null;
        }

        return PaymentStatus::fromLegacy((string) $raw);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function article(): BelongsTo
    {
        $foreignKey = $this->usesLegacySchema() ? 'actualite_id' : 'article_id';

        return $this->belongsTo(Article::class, $foreignKey);
    }

    public function subscriptionPlan(): BelongsTo
    {
        $foreignKey = $this->usesLegacySchema() ? 'plan_id' : 'subscription_plan_id';

        return $this->belongsTo(SubscriptionPlan::class, $foreignKey);
    }

    public function advertisement(): BelongsTo
    {
        $foreignKey = $this->usesLegacySchema() ? 'publicite_id' : 'advertisement_id';

        return $this->belongsTo(Advertisement::class, $foreignKey);
    }
}
