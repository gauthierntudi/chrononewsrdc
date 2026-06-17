<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AdvertisementManagementService
{
    /** @var array<string, string> */
    private const FORMAT_DIMENSIONS = [
        'rectangle' => '672x560',
        'portrait' => '512x562',
        'large_portrait' => '768x1024',
        'large_rectangle' => '1024x768',
        'paysage_small' => '1456x180',
        'paysage_medium' => '1920x400',
        'paysage_large' => '3456x502',
    ];

    /** @var array<string, string> */
    private const PLACEMENT_FORMAT_MAP = [
        'pub-header' => 'paysage_large',
        'pub-modal' => 'large_rectangle',
        'pub-float' => 'portrait',
        'pub-body-1' => 'paysage_small',
        'pub-body-2' => 'paysage_medium',
        'pub-body-3' => 'paysage_small',
        'pub-body-sidebar-1' => 'large_portrait',
        'pub-body-sidebar-2' => 'rectangle',
        'pub-footer' => 'paysage_large',
    ];

    public function usesLegacySchema(): bool
    {
        return Schema::hasTable('publicites');
    }

    public function paginate(
        int $page = 1,
        int $perPage = 10,
        ?string $search = null,
        ?string $validation = null,
        ?string $payment = null,
        ?string $broadcast = null,
        ?string $placement = null,
    ): LengthAwarePaginator {
        if (! $this->usesLegacySchema() && ! Schema::hasTable('advertisements')) {
            return new Paginator([], 0, $perPage, max($page, 1));
        }

        $query = $this->baseQuery();
        $this->applyFilters($query, $search, $validation, $payment, $broadcast, $placement);

        $total = (clone $query)->count('p.id');
        $perPage = min(max($perPage, 1), 50);
        $page = max($page, 1);

        $rows = $query
            ->orderByDesc('p.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $items = $rows->map(fn ($row) => $this->formatRow($row))->all();

        return new Paginator($items, $total, $perPage, $page);
    }

    /** @return array<string, int|float> */
    public function stats(): array
    {
        if ($this->usesLegacySchema()) {
            return $this->legacyStats();
        }

        if (Schema::hasTable('advertisements')) {
            return $this->modernStats();
        }

        return $this->emptyStats();
    }

    public function validate(int $id): array
    {
        $ad = $this->findOrFail($id);

        $paymentStatus = $this->usesLegacySchema()
            ? ($ad->statut_paiement ?? 'en_attente')
            : ($ad->payment_status ?? 'pending');

        $canActivate = in_array($paymentStatus, ['paye', 'gratuit', 'paid', 'free'], true);

        if ($this->usesLegacySchema()) {
            DB::table('publicites')->where('id', $id)->update([
                'statut_validation' => 'valide',
                'motif_refus' => null,
                'statut_diffusion' => $canActivate ? 'active' : ($ad->statut_diffusion ?? 'inactive'),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('advertisements')->where('id', $id)->update([
                'validation_status' => 'approved',
                'rejection_reason' => null,
                'broadcast_status' => $canActivate ? 'active' : ($ad->broadcast_status ?? 'inactive'),
                'updated_at' => now(),
            ]);
        }

        return ['success' => true, 'message' => 'Publicité validée'];
    }

    public function refuse(int $id, ?string $reason = null): array
    {
        $this->findOrFail($id);

        if ($reason !== null && trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => ['Le motif du refus est requis.'],
            ]);
        }

        if ($this->usesLegacySchema()) {
            DB::table('publicites')->where('id', $id)->update([
                'statut_validation' => 'refuse',
                'motif_refus' => $reason,
                'statut_diffusion' => 'inactive',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('advertisements')->where('id', $id)->update([
                'validation_status' => 'rejected',
                'rejection_reason' => $reason,
                'broadcast_status' => 'inactive',
                'updated_at' => now(),
            ]);
        }

        return ['success' => true, 'message' => 'Publicité refusée'];
    }

    public function setBroadcast(int $id, string $status): array
    {
        $this->findOrFail($id);

        if ($this->usesLegacySchema()) {
            if (! in_array($status, ['active', 'inactive', 'terminee'], true)) {
                throw ValidationException::withMessages(['status' => ['Statut de diffusion invalide.']]);
            }

            DB::table('publicites')->where('id', $id)->update([
                'statut_diffusion' => $status,
                'updated_at' => now(),
            ]);
        } else {
            $mapped = match ($status) {
                'active' => 'active',
                'inactive' => 'inactive',
                'terminee' => 'ended',
                default => throw ValidationException::withMessages(['status' => ['Statut de diffusion invalide.']]),
            };

            DB::table('advertisements')->where('id', $id)->update([
                'broadcast_status' => $mapped,
                'updated_at' => now(),
            ]);
        }

        $label = $status === 'active' ? 'activée' : ($status === 'inactive' ? 'désactivée' : 'terminée');

        return ['success' => true, 'message' => "Diffusion {$label}"];
    }

    public function delete(int $id): array
    {
        $this->findOrFail($id);

        if ($this->usesLegacySchema()) {
            DB::table('publicites')->where('id', $id)->delete();
        } else {
            DB::table('advertisements')->where('id', $id)->delete();
        }

        return ['success' => true, 'message' => 'Publicité supprimée'];
    }

    public function updateSchedule(int $id, string $startsAt, string $endsAt): array
    {
        return $this->update($id, [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }

    /** @return array<string, mixed> */
    public function show(int $id): array
    {
        $row = $this->baseQuery()->where('p.id', $id)->first();

        if (! $row) {
            throw ValidationException::withMessages([
                'id' => ['Publicité introuvable.'],
            ]);
        }

        return $this->formatRow($row);
    }

    /** @return array{success: bool, message: string} */
    public function update(int $id, array $data): array
    {
        $ad = $this->findOrFail($id);

        $validated = validator($data, [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'format' => ['sometimes', 'required', 'string', 'max:50'],
            'placement' => ['sometimes', 'required', 'string', 'max:100'],
            'image_url' => ['sometimes', 'required', 'string', 'max:500'],
            'target_url' => ['sometimes', 'required', 'url', 'max:500'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date', 'after_or_equal:starts_at'],
            'is_locked' => ['sometimes', 'boolean'],
        ])->validate();

        if (isset($validated['placement'], $validated['format'])) {
            $expectedFormat = self::PLACEMENT_FORMAT_MAP[$validated['placement']] ?? null;
            if ($expectedFormat && $validated['format'] !== $expectedFormat) {
                throw ValidationException::withMessages([
                    'format' => ['Le format ne correspond pas à l\'emplacement sélectionné.'],
                ]);
            }
        }

        $format = $validated['format'] ?? ($this->usesLegacySchema() ? $ad->format : $ad->format);
        $dimensions = self::FORMAT_DIMENSIONS[$format] ?? ($this->usesLegacySchema() ? ($ad->dimensions ?? '') : ($ad->dimensions ?? ''));

        if ($this->usesLegacySchema()) {
            $payload = array_filter([
                'titre' => $validated['title'] ?? null,
                'format' => $validated['format'] ?? null,
                'emplacement' => $validated['placement'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'url_cible' => $validated['target_url'] ?? null,
                'dimensions' => isset($validated['format']) ? $dimensions : null,
                'date_debut' => $validated['starts_at'] ?? null,
                'date_fin' => $validated['ends_at'] ?? null,
                'is_locked' => array_key_exists('is_locked', $validated) ? ($validated['is_locked'] ? 1 : 0) : null,
                'updated_at' => now(),
            ], fn ($value) => $value !== null);

            DB::table('publicites')->where('id', $id)->update($payload);
        } else {
            $payload = array_filter([
                'title' => $validated['title'] ?? null,
                'format' => $validated['format'] ?? null,
                'placement' => $validated['placement'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'target_url' => $validated['target_url'] ?? null,
                'dimensions' => isset($validated['format']) ? $dimensions : null,
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'is_locked' => array_key_exists('is_locked', $validated) ? (bool) $validated['is_locked'] : null,
                'updated_at' => now(),
            ], fn ($value) => $value !== null);

            DB::table('advertisements')->where('id', $id)->update($payload);
        }

        return ['success' => true, 'message' => 'Publicité mise à jour avec succès'];
    }

    protected function baseQuery()
    {
        if ($this->usesLegacySchema()) {
            return DB::table('publicites as p')
                ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
                ->select([
                    'p.*',
                    'u.nom as user_name',
                ]);
        }

        return DB::table('advertisements as p')
            ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
            ->select([
                'p.*',
                'u.nom as user_name',
            ]);
    }

    protected function applyFilters($query, ?string $search, ?string $validation, ?string $payment, ?string $broadcast, ?string $placement): void
    {
        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term): void {
                if ($this->usesLegacySchema()) {
                    $q->where('p.titre', 'like', $term)
                        ->orWhere('p.emplacement', 'like', $term)
                        ->orWhere('p.format', 'like', $term)
                        ->orWhere('u.nom', 'like', $term);
                } else {
                    $q->where('p.title', 'like', $term)
                        ->orWhere('p.placement', 'like', $term)
                        ->orWhere('p.format', 'like', $term)
                        ->orWhere('u.nom', 'like', $term);
                }
            });
        }

        if ($this->usesLegacySchema()) {
            if ($validation) {
                $query->where('p.statut_validation', $validation);
            }
            if ($payment) {
                $query->where('p.statut_paiement', $payment);
            }
            if ($broadcast) {
                $query->where('p.statut_diffusion', $broadcast);
            }
            if ($placement) {
                $query->where('p.emplacement', $placement);
            }

            return;
        }

        if ($validation) {
            $mapped = match ($validation) {
                'valide' => 'approved',
                'refuse' => 'rejected',
                'en_attente' => 'pending',
                default => $validation,
            };
            $query->where('p.validation_status', $mapped);
        }
        if ($payment) {
            $mapped = match ($payment) {
                'paye' => 'paid',
                'gratuit' => 'free',
                'en_attente' => 'pending',
                default => $payment,
            };
            $query->where('p.payment_status', $mapped);
        }
        if ($broadcast) {
            $mapped = match ($broadcast) {
                'terminee' => 'ended',
                default => $broadcast,
            };
            $query->where('p.broadcast_status', $mapped);
        }
        if ($placement) {
            $query->where('p.placement', $placement);
        }
    }

    protected function findOrFail(int $id): object
    {
        $table = $this->usesLegacySchema() ? 'publicites' : 'advertisements';
        $ad = DB::table($table)->where('id', $id)->first();

        if (! $ad) {
            throw ValidationException::withMessages([
                'id' => ['Publicité introuvable.'],
            ]);
        }

        return $ad;
    }

    protected function formatRow(object $row): array
    {
        if ($this->usesLegacySchema()) {
            return [
                'id' => (int) $row->id,
                'title' => $row->titre ?? '',
                'format' => $row->format ?? '',
                'placement' => $row->emplacement ?? '',
                'image_url' => $row->image_url ?? '',
                'target_url' => $row->url_cible ?? '',
                'starts_at' => $row->date_debut ?? null,
                'ends_at' => $row->date_fin ?? null,
                'amount_paid' => (float) ($row->montant_paye ?? 0),
                'payment_status' => $row->statut_paiement ?? 'en_attente',
                'validation_status' => $row->statut_validation ?? 'en_attente',
                'broadcast_status' => $row->statut_diffusion ?? 'inactive',
                'rejection_reason' => $row->motif_refus ?? null,
                'impressions' => (int) ($row->impressions ?? 0),
                'views' => (int) ($row->vues ?? 0),
                'clicks' => (int) ($row->clics ?? 0),
                'is_locked' => (bool) ($row->is_locked ?? false),
                'user_name' => $row->user_name ?? 'Inconnu',
                'created_at' => $row->created_at ?? null,
            ];
        }

        return [
            'id' => (int) $row->id,
            'title' => $row->title ?? '',
            'format' => $row->format ?? '',
            'placement' => $row->placement ?? '',
            'image_url' => $row->image_url ?? '',
            'target_url' => $row->target_url ?? '',
            'starts_at' => $row->starts_at ?? null,
            'ends_at' => $row->ends_at ?? null,
            'amount_paid' => (float) ($row->amount_paid ?? 0),
            'payment_status' => match ($row->payment_status ?? 'pending') {
                'paid' => 'paye',
                'free' => 'gratuit',
                'pending' => 'en_attente',
                default => (string) ($row->payment_status ?? 'en_attente'),
            },
            'validation_status' => match ($row->validation_status ?? 'pending') {
                'approved' => 'valide',
                'rejected' => 'refuse',
                'pending' => 'en_attente',
                default => (string) ($row->validation_status ?? 'en_attente'),
            },
            'broadcast_status' => match ($row->broadcast_status ?? 'inactive') {
                'ended' => 'terminee',
                default => (string) ($row->broadcast_status ?? 'inactive'),
            },
            'rejection_reason' => $row->rejection_reason ?? null,
            'impressions' => (int) ($row->impressions ?? 0),
            'views' => (int) ($row->views ?? 0),
            'clicks' => (int) ($row->clicks ?? 0),
            'is_locked' => (bool) ($row->is_locked ?? false),
            'user_name' => $row->user_name ?? 'Inconnu',
            'created_at' => $row->created_at ?? null,
        ];
    }

    /** @return array<string, int|float> */
    protected function legacyStats(): array
    {
        $row = DB::table('publicites')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN statut_validation = 'en_attente' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN statut_validation = 'valide' THEN 1 ELSE 0 END) as validated,
                SUM(CASE WHEN statut_diffusion = 'active' THEN 1 ELSE 0 END) as active,
                COALESCE(SUM(montant_paye), 0) as revenue,
                COALESCE(SUM(impressions), 0) as impressions
            ")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'pending' => (int) ($row->pending ?? 0),
            'validated' => (int) ($row->validated ?? 0),
            'active' => (int) ($row->active ?? 0),
            'revenue' => (float) ($row->revenue ?? 0),
            'impressions' => (int) ($row->impressions ?? 0),
        ];
    }

    /** @return array<string, int|float> */
    protected function modernStats(): array
    {
        $row = DB::table('advertisements')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN validation_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN validation_status = 'approved' THEN 1 ELSE 0 END) as validated,
                SUM(CASE WHEN broadcast_status = 'active' THEN 1 ELSE 0 END) as active,
                COALESCE(SUM(amount_paid), 0) as revenue,
                COALESCE(SUM(impressions), 0) as impressions
            ")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'pending' => (int) ($row->pending ?? 0),
            'validated' => (int) ($row->validated ?? 0),
            'active' => (int) ($row->active ?? 0),
            'revenue' => (float) ($row->revenue ?? 0),
            'impressions' => (int) ($row->impressions ?? 0),
        ];
    }

    /** @return array<string, int|float> */
    protected function emptyStats(): array
    {
        return [
            'total' => 0,
            'pending' => 0,
            'validated' => 0,
            'active' => 0,
            'revenue' => 0,
            'impressions' => 0,
        ];
    }
}
