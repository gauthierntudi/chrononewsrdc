<?php

namespace App\Services\Advertisement;

use App\Models\User;
use App\Services\Admin\AdvertisementRateManagementService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class UserAdvertisementService
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

    public function __construct(
        protected AdvertisementRateManagementService $rates,
    ) {}

    public function usesLegacySchema(): bool
    {
        return Schema::hasTable('publicites');
    }

    /** @return list<array<string, mixed>> */
    public function listForUser(User $user, array $filters = []): array
    {
        if (! $this->usesLegacySchema() && ! Schema::hasTable('advertisements')) {
            return [];
        }

        return $this->userQuery($user, $filters)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($row) => $this->formatRow($row))
            ->all();
    }

    public function paginateForUser(
        User $user,
        int $page = 1,
        int $perPage = 10,
        array $filters = [],
    ): LengthAwarePaginator {
        if (! $this->usesLegacySchema() && ! Schema::hasTable('advertisements')) {
            return new Paginator([], 0, $perPage, max($page, 1));
        }

        $query = $this->userQuery($user, $filters);
        $total = (clone $query)->count();
        $perPage = min(max($perPage, 1), 50);
        $page = max($page, 1);

        $items = $query
            ->orderByDesc('created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn ($row) => $this->formatRow($row))
            ->all();

        return new Paginator($items, $total, $perPage, $page);
    }

    /** @return array<string, int|float> */
    public function statsForUser(User $user): array
    {
        $ads = $this->listForUser($user);

        return [
            'total' => count($ads),
            'pending' => count(array_filter($ads, fn ($ad) => ($ad['validation_status'] ?? '') === 'en_attente')),
            'validated' => count(array_filter($ads, fn ($ad) => ($ad['validation_status'] ?? '') === 'valide')),
            'active' => count(array_filter($ads, fn ($ad) => ($ad['broadcast_status'] ?? '') === 'active')),
            'revenue' => array_sum(array_map(fn ($ad) => (float) ($ad['amount_paid'] ?? 0), $ads)),
            'views' => array_sum(array_map(fn ($ad) => (int) ($ad['views'] ?? 0), $ads)),
        ];
    }

    /** @param  array<string, string|null>  $filters */
    protected function userQuery(User $user, array $filters = [])
    {
        $table = $this->usesLegacySchema() ? 'publicites' : 'advertisements';
        $query = DB::table($table)->where('user_id', $user->id);
        $this->applyUserFilters($query, $filters);

        return $query;
    }

    /** @param  array<string, string|null>  $filters */
    protected function applyUserFilters($query, array $filters): void
    {
        $search = $filters['search'] ?? null;
        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term): void {
                if ($this->usesLegacySchema()) {
                    $q->where('titre', 'like', $term)
                        ->orWhere('emplacement', 'like', $term)
                        ->orWhere('format', 'like', $term);
                } else {
                    $q->where('title', 'like', $term)
                        ->orWhere('placement', 'like', $term)
                        ->orWhere('format', 'like', $term);
                }
            });
        }

        if ($this->usesLegacySchema()) {
            if (! empty($filters['validation'])) {
                $query->where('statut_validation', $filters['validation']);
            }
            if (! empty($filters['payment'])) {
                $query->where('statut_paiement', $filters['payment']);
            }
            if (! empty($filters['broadcast'])) {
                $query->where('statut_diffusion', $filters['broadcast']);
            }
            if (! empty($filters['placement'])) {
                $query->where('emplacement', $filters['placement']);
            }

            return;
        }

        if (! empty($filters['validation'])) {
            $query->where('validation_status', $this->mapValidationFilter($filters['validation']));
        }
        if (! empty($filters['payment'])) {
            $query->where('payment_status', $this->mapPaymentFilter($filters['payment']));
        }
        if (! empty($filters['broadcast'])) {
            $query->where('broadcast_status', $this->mapBroadcastFilter($filters['broadcast']));
        }
        if (! empty($filters['placement'])) {
            $query->where('placement', $filters['placement']);
        }
    }

    /** @return array{success: bool, message: string, advertisement_id?: int, amount?: float, requires_payment?: bool} */
    public function create(User $user, array $data): array
    {
        $staffAd = $user->role?->canManageAllAds() ?? false;

        $validated = validator($data, [
            'title' => ['required', 'string', 'max:255'],
            'format' => ['required', 'string', 'max:50'],
            'image_url' => ['required', 'string', 'max:500'],
            'target_url' => ['required', 'url', 'max:500'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'placement' => [$staffAd ? 'required' : 'nullable', 'string', 'max:100'],
            'is_locked' => ['sometimes', 'boolean'],
        ])->validate();

        $isLocked = $staffAd && (bool) ($validated['is_locked'] ?? false);

        if (! $this->usesLegacySchema() && ! Schema::hasTable('advertisements')) {
            throw ValidationException::withMessages([
                'advertisement' => ['Table publicites introuvable.'],
            ]);
        }

        $isFree = $user->role?->adsAreFree() ?? false;
        $isSuperAdmin = $user->isSuperAdmin();
        $duration = $this->resolveDurationDays($validated['starts_at'], $validated['ends_at']);
        $amount = $isFree ? 0.0 : $this->resolvePrice($validated['format'], $duration);
        $dimensions = self::FORMAT_DIMENSIONS[$validated['format']] ?? '';

        if ($this->usesLegacySchema()) {
            $id = DB::table('publicites')->insertGetId([
                'user_id' => $user->id,
                'titre' => $validated['title'],
                'format' => $validated['format'],
                'emplacement' => $validated['placement'] ?? null,
                'image_url' => $validated['image_url'],
                'url_cible' => $validated['target_url'],
                'dimensions' => $dimensions,
                'date_debut' => $validated['starts_at'],
                'date_fin' => $validated['ends_at'],
                'montant_paye' => $amount,
                'statut_paiement' => $isFree ? 'gratuit' : 'en_attente',
                'statut_validation' => $isSuperAdmin ? 'valide' : 'en_attente',
                'statut_diffusion' => $isSuperAdmin ? 'active' : 'inactive',
                'is_locked' => $isLocked ? 1 : 0,
                'created_by_admin' => $staffAd ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $id = DB::table('advertisements')->insertGetId([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'format' => $validated['format'],
                'placement' => $validated['placement'] ?? null,
                'image_url' => $validated['image_url'],
                'target_url' => $validated['target_url'],
                'dimensions' => $dimensions,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'amount_paid' => $amount,
                'payment_status' => $isFree ? 'free' : 'pending',
                'validation_status' => $isSuperAdmin ? 'approved' : 'pending',
                'broadcast_status' => $isSuperAdmin ? 'active' : 'inactive',
                'is_locked' => $isLocked,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $message = match (true) {
            $isSuperAdmin => 'Publicité créée, validée et activée automatiquement',
            $staffAd => 'Publicité créée avec succès',
            $isFree => 'Publicité créée avec succès. En attente de validation.',
            default => 'Publicité créée. Veuillez procéder au paiement.',
        };

        return [
            'success' => true,
            'message' => $message,
            'advertisement_id' => (int) $id,
            'amount' => $amount,
            'requires_payment' => ! $isFree && ! $isSuperAdmin,
        ];
    }

    /** @return array{success: bool, message: string, requires_payment?: bool} */
    public function update(User $user, int $id, array $data): array
    {
        $ad = $this->findOwnedOrFail($user, $id);

        $staffAd = $user->role?->canManageAllAds() ?? false;

        $validated = validator($data, [
            'title' => ['sometimes', 'string', 'max:255'],
            'format' => ['sometimes', 'string', 'max:50'],
            'image_url' => ['sometimes', 'string', 'max:500'],
            'target_url' => ['sometimes', 'url', 'max:500'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date'],
            'placement' => ['nullable', 'string', 'max:100'],
            'is_locked' => ['sometimes', 'boolean'],
        ])->validate();

        $startsAt = $validated['starts_at'] ?? ($this->usesLegacySchema() ? $ad->date_debut : $ad->starts_at);
        $endsAt = $validated['ends_at'] ?? ($this->usesLegacySchema() ? $ad->date_fin : $ad->ends_at);
        $format = $validated['format'] ?? ($this->usesLegacySchema() ? $ad->format : $ad->format);

        $isFree = $user->role?->adsAreFree() ?? false;
        $duration = $this->resolveDurationDays($startsAt, $endsAt);
        $amount = $isFree ? 0.0 : $this->resolvePrice($format, $duration);
        $paymentStatus = $this->usesLegacySchema() ? ($ad->statut_paiement ?? 'en_attente') : ($ad->payment_status ?? 'pending');

        if (! $isFree && (float) ($this->usesLegacySchema() ? $ad->montant_paye : $ad->amount_paid) !== $amount) {
            $paymentStatus = $this->usesLegacySchema() ? 'en_attente' : 'pending';
        }

        $payload = $this->usesLegacySchema()
            ? array_filter([
                'titre' => $validated['title'] ?? null,
                'format' => $validated['format'] ?? null,
                'emplacement' => array_key_exists('placement', $validated) ? $validated['placement'] : null,
                'image_url' => $validated['image_url'] ?? null,
                'url_cible' => $validated['target_url'] ?? null,
                'dimensions' => isset($validated['format']) ? (self::FORMAT_DIMENSIONS[$format] ?? '') : null,
                'date_debut' => $validated['starts_at'] ?? null,
                'date_fin' => $validated['ends_at'] ?? null,
                'montant_paye' => $amount,
                'statut_paiement' => $paymentStatus,
                'statut_validation' => 'en_attente',
                'statut_diffusion' => 'inactive',
                'is_locked' => array_key_exists('is_locked', $validated) && $staffAd ? ($validated['is_locked'] ? 1 : 0) : null,
                'updated_at' => now(),
            ], fn ($value) => $value !== null)
            : array_filter([
                'title' => $validated['title'] ?? null,
                'format' => $validated['format'] ?? null,
                'placement' => array_key_exists('placement', $validated) ? $validated['placement'] : null,
                'image_url' => $validated['image_url'] ?? null,
                'target_url' => $validated['target_url'] ?? null,
                'dimensions' => isset($validated['format']) ? (self::FORMAT_DIMENSIONS[$format] ?? '') : null,
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'amount_paid' => $amount,
                'payment_status' => $paymentStatus,
                'validation_status' => 'pending',
                'broadcast_status' => 'inactive',
                'is_locked' => array_key_exists('is_locked', $validated) && $staffAd ? (bool) $validated['is_locked'] : null,
                'updated_at' => now(),
            ], fn ($value) => $value !== null);

        $table = $this->usesLegacySchema() ? 'publicites' : 'advertisements';
        DB::table($table)->where('id', $id)->update($payload);

        return [
            'success' => true,
            'message' => 'Publicité mise à jour avec succès',
            'requires_payment' => ! $isFree && in_array($paymentStatus, ['en_attente', 'pending'], true),
        ];
    }

    /** @return array<string, mixed> */
    public function showForUser(User $user, int $id): array
    {
        return $this->formatRow($this->findOwnedOrFail($user, $id));
    }

    /** @return array{success: bool, message: string} */
    public function delete(User $user, int $id): array
    {
        $ad = $this->findOwnedOrFail($user, $id);

        if ((bool) ($this->usesLegacySchema() ? ($ad->is_locked ?? false) : ($ad->is_locked ?? false))) {
            throw ValidationException::withMessages([
                'id' => ['Cette publicité est verrouillée et ne peut pas être supprimée.'],
            ]);
        }

        $table = $this->usesLegacySchema() ? 'publicites' : 'advertisements';
        DB::table($table)->where('id', $id)->delete();

        return ['success' => true, 'message' => 'Publicité supprimée'];
    }

    protected function findOwnedOrFail(User $user, int $id): object
    {
        $table = $this->usesLegacySchema() ? 'publicites' : 'advertisements';
        $ad = DB::table($table)->where('id', $id)->where('user_id', $user->id)->first();

        if (! $ad) {
            throw ValidationException::withMessages([
                'id' => ['Publicité introuvable ou accès refusé.'],
            ]);
        }

        return $ad;
    }

    protected function resolveDurationDays(string $startsAt, string $endsAt): int
    {
        $days = Carbon::parse($startsAt)->diffInDays(Carbon::parse($endsAt)) + 1;

        if ($days <= 7) {
            return 7;
        }

        if ($days <= 15) {
            return 15;
        }

        return 30;
    }

    protected function resolvePrice(string $format, int $durationDays): float
    {
        $rate = collect($this->rates->list())->firstWhere('format', $format);

        if (! $rate) {
            throw ValidationException::withMessages([
                'format' => ['Format de publicité invalide.'],
            ]);
        }

        return match ($durationDays) {
            7 => (float) $rate['price_7_days'],
            15 => (float) $rate['price_15_days'],
            default => (float) $rate['price_30_days'],
        };
    }

    /** @return array<string, mixed> */
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
                'dimensions' => $row->dimensions ?? '',
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
            'dimensions' => $row->dimensions ?? '',
            'starts_at' => $row->starts_at ?? null,
            'ends_at' => $row->ends_at ?? null,
            'amount_paid' => (float) ($row->amount_paid ?? 0),
            'payment_status' => match ($row->payment_status ?? 'pending') {
                'paid' => 'paye',
                'free' => 'gratuit',
                default => 'en_attente',
            },
            'validation_status' => match ($row->validation_status ?? 'pending') {
                'approved' => 'valide',
                'rejected' => 'refuse',
                default => 'en_attente',
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
            'created_at' => $row->created_at ?? null,
        ];
    }

    protected function mapValidationFilter(string $value): string
    {
        return match ($value) {
            'valide' => 'approved',
            'refuse' => 'rejected',
            default => 'pending',
        };
    }

    protected function mapPaymentFilter(string $value): string
    {
        return match ($value) {
            'paye' => 'paid',
            'gratuit' => 'free',
            default => 'pending',
        };
    }

    protected function mapBroadcastFilter(string $value): string
    {
        return $value === 'terminee' ? 'ended' : $value;
    }
}
