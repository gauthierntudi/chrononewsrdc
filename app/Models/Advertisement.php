<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Advertisement extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return Schema::hasTable('publicites') ? 'publicites' : 'advertisements';
    }

    public function usesLegacySchema(): bool
    {
        return $this->getTable() === 'publicites';
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'amount_paid' => 'decimal:2',
            'is_locked' => 'boolean',
            'created_by_admin' => 'boolean',
            'impressions' => 'integer',
            'clicks' => 'integer',
        ];
    }

    public function getTitleAttribute(): ?string
    {
        return $this->attributes['title'] ?? $this->attributes['titre'] ?? null;
    }

    public function getPlacementAttribute(): ?string
    {
        return $this->attributes['placement'] ?? $this->attributes['emplacement'] ?? null;
    }

    public function getTargetUrlAttribute(): ?string
    {
        return $this->attributes['target_url'] ?? $this->attributes['url_cible'] ?? null;
    }

    public function getStartsAtAttribute(): mixed
    {
        $value = $this->attributes['starts_at'] ?? $this->attributes['date_debut'] ?? null;

        return $value ? $this->asDateTime($value) : null;
    }

    public function getEndsAtAttribute(): mixed
    {
        $value = $this->attributes['ends_at'] ?? $this->attributes['date_fin'] ?? null;

        return $value ? $this->asDateTime($value) : null;
    }

    public function getAmountPaidAttribute(): ?float
    {
        $value = $this->attributes['amount_paid'] ?? $this->attributes['montant_paye'] ?? null;

        return $value !== null ? (float) $value : null;
    }

    public function getPaymentStatusAttribute(): ?string
    {
        return $this->attributes['payment_status'] ?? $this->attributes['statut_paiement'] ?? null;
    }

    public function getValidationStatusAttribute(): ?string
    {
        return $this->attributes['validation_status'] ?? $this->attributes['statut_validation'] ?? null;
    }

    public function getBroadcastStatusAttribute(): ?string
    {
        return $this->attributes['broadcast_status'] ?? $this->attributes['statut_diffusion'] ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        $foreignKey = $this->usesLegacySchema() ? 'publicite_id' : 'advertisement_id';

        return $this->hasMany(Payment::class, $foreignKey);
    }

    public function scopeActive(Builder $query): Builder
    {
        if ((new static)->usesLegacySchema()) {
            return $query
                ->where('statut_validation', 'valide')
                ->where('statut_diffusion', 'active')
                ->whereIn('statut_paiement', ['paye', 'gratuit'])
                ->whereDate('date_debut', '<=', today())
                ->whereDate('date_fin', '>=', today());
        }

        return $query
            ->where('validation_status', 'approved')
            ->where('broadcast_status', 'active')
            ->where('payment_status', 'paid')
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today());
    }
}
