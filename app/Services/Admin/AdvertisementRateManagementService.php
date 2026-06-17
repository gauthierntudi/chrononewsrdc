<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AdvertisementRateManagementService
{
    public function usesLegacySchema(): bool
    {
        return Schema::hasTable('tarifs_publicites');
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        if (! $this->usesLegacySchema() && ! Schema::hasTable('advertisement_rates')) {
            return [];
        }

        $query = DB::table($this->tableName());

        if ($this->usesLegacySchema()) {
            $query->where('actif', 1);
        } else {
            $query->where('is_active', true);
        }

        return $query
            ->orderBy('format')
            ->get()
            ->map(fn ($row) => $this->formatRow($row))
            ->all();
    }

    public function update(int $id, float $price7, float $price15, float $price30): void
    {
        if ($price7 < 0 || $price15 < 0 || $price30 < 0) {
            throw ValidationException::withMessages([
                'price' => 'Les prix doivent être positifs.',
            ]);
        }

        if (! $this->usesLegacySchema() && ! Schema::hasTable('advertisement_rates')) {
            throw ValidationException::withMessages([
                'rate' => 'Table des tarifs introuvable.',
            ]);
        }

        $updated = DB::table($this->tableName())
            ->where('id', $id)
            ->update($this->usesLegacySchema()
                ? [
                    'prix_7_jours' => $price7,
                    'prix_15_jours' => $price15,
                    'prix_30_jours' => $price30,
                    'updated_at' => now(),
                ]
                : [
                    'price_7_days' => $price7,
                    'price_15_days' => $price15,
                    'price_30_days' => $price30,
                    'updated_at' => now(),
                ]);

        if (! $updated) {
            throw ValidationException::withMessages([
                'rate' => 'Tarif introuvable.',
            ]);
        }
    }

    protected function tableName(): string
    {
        return $this->usesLegacySchema() ? 'tarifs_publicites' : 'advertisement_rates';
    }

    protected function formatRow(object $row): array
    {
        if ($this->usesLegacySchema()) {
            return [
                'id' => (int) $row->id,
                'format' => $row->format,
                'label' => $this->formatLabel($row->format),
                'dimensions' => $row->dimensions,
                'price_7_days' => (float) $row->prix_7_jours,
                'price_15_days' => (float) $row->prix_15_jours,
                'price_30_days' => (float) $row->prix_30_jours,
                'is_active' => (bool) $row->actif,
            ];
        }

        return [
            'id' => (int) $row->id,
            'format' => $row->format,
            'label' => $row->label ?? $this->formatLabel($row->format),
            'dimensions' => $row->dimensions,
            'price_7_days' => (float) $row->price_7_days,
            'price_15_days' => (float) $row->price_15_days,
            'price_30_days' => (float) $row->price_30_days,
            'is_active' => (bool) $row->is_active,
        ];
    }

    protected function formatLabel(string $format): string
    {
        return match ($format) {
            'rectangle' => 'Rectangle',
            'portrait' => 'Portrait',
            'paysage_small' => 'Paysage Small',
            'paysage_medium' => 'Paysage Medium',
            'paysage_large' => 'Paysage Large',
            'large_portrait' => 'Large Portrait',
            'large_rectangle' => 'Large Rectangle',
            default => ucfirst(str_replace('_', ' ', $format)),
        };
    }
}
