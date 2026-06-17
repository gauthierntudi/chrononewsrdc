<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentManagementService
{
    public function usesLegacySchema(): bool
    {
        return Schema::hasTable('paiements');
    }

    public function paginate(int $page = 1, int $perPage = 10, ?string $search = null, ?string $status = null, ?int $userId = null): LengthAwarePaginator
    {
        if ($this->usesLegacySchema()) {
            return $this->paginateLegacy($page, $perPage, $search, $status, $userId);
        }

        if (Schema::hasTable('payments')) {
            return $this->paginateModern($page, $perPage, $search, $status, $userId);
        }

        return new Paginator([], 0, $perPage, max($page, 1));
    }

    /** @return array<string, int|float> */
    public function stats(?int $userId = null): array
    {
        if ($this->usesLegacySchema()) {
            return $this->legacyStats($userId);
        }

        if (Schema::hasTable('payments')) {
            return $this->modernStats($userId);
        }

        return $this->emptyStats();
    }

    protected function paginateLegacy(int $page, int $perPage, ?string $search, ?string $status, ?int $userId = null): LengthAwarePaginator
    {
        $query = $this->legacyBaseQuery($userId);
        $this->applyLegacyFilters($query, $search, $status);

        $total = (clone $query)->distinct()->count('p.id');
        $perPage = min(max($perPage, 1), 50);
        $page = max($page, 1);

        $select = [
            'p.id',
            'p.montant as amount',
            'p.methode as method',
            'p.statut as status',
            'p.transaction_id',
            'p.created_at',
            'u.nom as user_name',
            'u.mail as user_email',
            'a.titre as article_title',
            'a.cover as article_cover',
        ];

        if (Schema::hasTable('publicites') && Schema::hasColumn('paiements', 'publicite_id')) {
            $select[] = 'pub.titre as advertisement_title';
            $select[] = 'pub.image_url as advertisement_cover';
            $select[] = 'p.publicite_id';
        }

        if (Schema::hasTable('subscription_plans') && Schema::hasColumn('paiements', 'plan_id')) {
            $select[] = 'sp.name as plan_name';
            $select[] = 'p.plan_id';
        }

        $select[] = 'p.actualite_id as article_id';

        $rows = $query
            ->select($select)
            ->orderByDesc('p.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $items = $rows->map(fn ($row) => $this->formatLegacyRow($row))->all();

        return new Paginator($items, $total, $perPage, $page);
    }

    protected function paginateModern(int $page, int $perPage, ?string $search, ?string $status, ?int $userId = null): LengthAwarePaginator
    {
        $query = DB::table('payments as p')
            ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
            ->leftJoin('articles as a', 'p.article_id', '=', 'a.id');

        if ($userId !== null) {
            $query->where('p.user_id', $userId);
        }

        if (Schema::hasTable('advertisements')) {
            $query->leftJoin('advertisements as pub', 'p.advertisement_id', '=', 'pub.id');
        }

        if (Schema::hasTable('subscription_plans')) {
            $query->leftJoin('subscription_plans as sp', 'p.subscription_plan_id', '=', 'sp.id');
        }

        $this->applyModernFilters($query, $search, $status);

        $total = (clone $query)->distinct()->count('p.id');
        $perPage = min(max($perPage, 1), 50);
        $page = max($page, 1);

        $select = [
            'p.id',
            'p.amount',
            'p.method',
            'p.status',
            'p.transaction_id',
            'p.created_at',
            'u.nom as user_name',
            'u.mail as user_email',
            'a.title as article_title',
            'a.cover as article_cover',
            'p.article_id',
        ];

        if (Schema::hasTable('advertisements')) {
            $select[] = 'pub.title as advertisement_title';
            $select[] = 'pub.image_url as advertisement_cover';
            $select[] = 'p.advertisement_id';
        }

        if (Schema::hasTable('subscription_plans')) {
            $select[] = 'sp.name as plan_name';
            $select[] = 'p.subscription_plan_id as plan_id';
        }

        $rows = $query
            ->select($select)
            ->orderByDesc('p.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $items = $rows->map(fn ($row) => $this->formatModernRow($row))->all();

        return new Paginator($items, $total, $perPage, $page);
    }

    protected function legacyBaseQuery(?int $userId = null)
    {
        $query = DB::table('paiements as p')
            ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
            ->leftJoin('actualites as a', 'p.actualite_id', '=', 'a.id');

        if ($userId !== null) {
            $query->where('p.user_id', $userId);
        }

        if (Schema::hasTable('publicites') && Schema::hasColumn('paiements', 'publicite_id')) {
            $query->leftJoin('publicites as pub', 'p.publicite_id', '=', 'pub.id');
        }

        if (Schema::hasTable('subscription_plans') && Schema::hasColumn('paiements', 'plan_id')) {
            $query->leftJoin('subscription_plans as sp', 'p.plan_id', '=', 'sp.id');
        }

        return $query;
    }

    protected function applyLegacyFilters($query, ?string $search, ?string $status): void
    {
        if ($status && $status !== 'all') {
            $query->where('p.statut', $status);
        }

        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('a.titre', 'like', $term)
                    ->orWhere('p.transaction_id', 'like', $term)
                    ->orWhere('u.nom', 'like', $term)
                    ->orWhere('u.mail', 'like', $term);

                if (Schema::hasTable('publicites')) {
                    $q->orWhere('pub.titre', 'like', $term);
                }

                if (Schema::hasTable('subscription_plans')) {
                    $q->orWhere('sp.name', 'like', $term);
                }
            });
        }
    }

    protected function applyModernFilters($query, ?string $search, ?string $status): void
    {
        if ($status && $status !== 'all') {
            $legacyStatus = match ($status) {
                'reussi' => 'succeeded',
                'echoue' => 'failed',
                'en_attente' => 'pending',
                default => $status,
            };
            $query->where('p.status', $legacyStatus);
        }

        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('a.title', 'like', $term)
                    ->orWhere('p.transaction_id', 'like', $term)
                    ->orWhere('u.nom', 'like', $term)
                    ->orWhere('u.mail', 'like', $term);

                if (Schema::hasTable('advertisements')) {
                    $q->orWhere('pub.title', 'like', $term);
                }

                if (Schema::hasTable('subscription_plans')) {
                    $q->orWhere('sp.name', 'like', $term);
                }
            });
        }
    }

    protected function formatLegacyRow(object $row): array
    {
        $type = 'unknown';
        $title = 'Élément inconnu';
        $cover = null;

        if (! empty($row->publicite_id)) {
            $type = 'publicite';
            $title = $row->advertisement_title ?: 'Publicité sans titre';
            $cover = $row->advertisement_cover ?? null;
        } elseif (! empty($row->article_id)) {
            $type = 'article';
            $title = $row->article_title ?: 'Article sans titre';
            $cover = $row->article_cover ?? null;
        } elseif (! empty($row->plan_id)) {
            $type = 'abonnement';
            $title = 'Abonnement '.($row->plan_name ?? '');
            $cover = null;
        }

        return [
            'id' => (int) $row->id,
            'type' => $type,
            'title' => $title,
            'cover' => $cover,
            'user_name' => $row->user_name ?? 'Inconnu',
            'user_email' => $row->user_email ?? '',
            'method' => $row->method ?? '',
            'status' => $row->status ?? 'en_attente',
            'amount' => (float) ($row->amount ?? 0),
            'transaction_id' => $row->transaction_id ?? '',
            'created_at' => $row->created_at,
        ];
    }

    protected function formatModernRow(object $row): array
    {
        $type = 'unknown';
        $title = 'Élément inconnu';
        $cover = null;

        if (! empty($row->advertisement_id)) {
            $type = 'publicite';
            $title = $row->advertisement_title ?: 'Publicité sans titre';
            $cover = $row->advertisement_cover ?? null;
        } elseif (! empty($row->article_id)) {
            $type = 'article';
            $title = $row->article_title ?: 'Article sans titre';
            $cover = $row->article_cover ?? null;
        } elseif (! empty($row->plan_id)) {
            $type = 'abonnement';
            $title = 'Abonnement '.($row->plan_name ?? '');
            $cover = null;
        }

        $status = match ($row->status ?? 'pending') {
            'succeeded' => 'reussi',
            'failed' => 'echoue',
            'pending' => 'en_attente',
            default => (string) ($row->status ?? 'en_attente'),
        };

        return [
            'id' => (int) $row->id,
            'type' => $type,
            'title' => $title,
            'cover' => $cover,
            'user_name' => $row->user_name ?? 'Inconnu',
            'user_email' => $row->user_email ?? '',
            'method' => $row->method ?? '',
            'status' => $status,
            'amount' => (float) ($row->amount ?? 0),
            'transaction_id' => $row->transaction_id ?? '',
            'created_at' => $row->created_at,
        ];
    }

    /** @return array<string, mixed> */
    public function transactionHistory(?int $userId = null): array
    {
        if ($this->usesLegacySchema()) {
            return $this->legacyTransactionHistory($userId);
        }

        if (Schema::hasTable('payments')) {
            return $this->modernTransactionHistory($userId);
        }

        return $this->emptyChart();
    }

    /** @return array<string, int|float> */
    protected function legacyStats(?int $userId = null): array
    {
        $query = DB::table('paiements');
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return [
            'total_count' => (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('montant'),
            'succeeded_count' => (clone $query)->where('statut', 'reussi')->count(),
            'succeeded_amount' => (float) (clone $query)->where('statut', 'reussi')->sum('montant'),
            'failed_count' => (clone $query)->where('statut', 'echoue')->count(),
            'failed_amount' => (float) (clone $query)->where('statut', 'echoue')->sum('montant'),
            'pending_count' => (clone $query)->where('statut', 'en_attente')->count(),
            'pending_amount' => (float) (clone $query)->where('statut', 'en_attente')->sum('montant'),
            'chart' => $this->legacyTransactionHistory($userId),
        ];
    }

    /** @return array<string, int|float> */
    protected function modernStats(?int $userId = null): array
    {
        $query = DB::table('payments');
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return [
            'total_count' => (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('amount'),
            'succeeded_count' => (clone $query)->where('status', 'succeeded')->count(),
            'succeeded_amount' => (float) (clone $query)->where('status', 'succeeded')->sum('amount'),
            'failed_count' => (clone $query)->where('status', 'failed')->count(),
            'failed_amount' => (float) (clone $query)->where('status', 'failed')->sum('amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'pending_amount' => (float) (clone $query)->where('status', 'pending')->sum('amount'),
            'chart' => $this->modernTransactionHistory($userId),
        ];
    }

    /** @return array<string, array<string, array<int, float|int|string>>> */
    protected function legacyTransactionHistory(?int $userId = null): array
    {
        return [
            'monthly' => $this->historyByMonth('paiements', 'montant', 'statut', 'reussi', userId: $userId),
            'weekly' => $this->historyByWeek('paiements', 'montant', 'statut', 'reussi', userId: $userId),
            'daily' => $this->historyByDay('paiements', 'montant', 'statut', 'reussi', userId: $userId),
        ];
    }

    /** @return array<string, array<string, array<int, float|int|string>>> */
    protected function modernTransactionHistory(?int $userId = null): array
    {
        return [
            'monthly' => $this->historyByMonth('payments', 'amount', 'status', 'succeeded', userId: $userId),
            'weekly' => $this->historyByWeek('payments', 'amount', 'status', 'succeeded', userId: $userId),
            'daily' => $this->historyByDay('payments', 'amount', 'status', 'succeeded', userId: $userId),
        ];
    }

    /**
     * @return array{labels: array<int, string>, amounts: array<int, float>, counts: array<int, int>}
     */
    protected function historyByMonth(string $table, string $amountColumn, string $statusColumn, string $successValue, int $months = 6, ?int $userId = null): array
    {
        $labels = [];
        $amounts = [];
        $counts = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = ucfirst($month->copy()->locale('fr')->translatedFormat('M'));
            [$amount, $count] = $this->periodTotals(
                $table,
                $amountColumn,
                $statusColumn,
                $successValue,
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
                $userId,
            );
            $amounts[] = $amount;
            $counts[] = $count;
        }

        return compact('labels', 'amounts', 'counts');
    }

    /**
     * @return array{labels: array<int, string>, amounts: array<int, float>, counts: array<int, int>}
     */
    protected function historyByWeek(string $table, string $amountColumn, string $statusColumn, string $successValue, int $weeks = 8, ?int $userId = null): array
    {
        $labels = [];
        $amounts = [];
        $counts = [];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $labels[] = 'S'.$weekStart->isoWeek;
            [$amount, $count] = $this->periodTotals(
                $table,
                $amountColumn,
                $statusColumn,
                $successValue,
                $weekStart,
                $weekEnd,
                $userId,
            );
            $amounts[] = $amount;
            $counts[] = $count;
        }

        return compact('labels', 'amounts', 'counts');
    }

    /**
     * @return array{labels: array<int, string>, amounts: array<int, float>, counts: array<int, int>}
     */
    protected function historyByDay(string $table, string $amountColumn, string $statusColumn, string $successValue, int $days = 7, ?int $userId = null): array
    {
        $labels = [];
        $amounts = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = ucfirst($day->copy()->locale('fr')->translatedFormat('D'));
            [$amount, $count] = $this->periodTotals(
                $table,
                $amountColumn,
                $statusColumn,
                $successValue,
                $day->copy()->startOfDay(),
                $day->copy()->endOfDay(),
                $userId,
            );
            $amounts[] = $amount;
            $counts[] = $count;
        }

        return compact('labels', 'amounts', 'counts');
    }

    /** @return array{0: float, 1: int} */
    protected function periodTotals(
        string $table,
        string $amountColumn,
        string $statusColumn,
        string $successValue,
        Carbon $start,
        Carbon $end,
        ?int $userId = null,
    ): array {
        $period = DB::table($table)->whereBetween('created_at', [$start, $end]);

        if ($userId !== null) {
            $period->where('user_id', $userId);
        }

        return [
            (float) (clone $period)->where($statusColumn, $successValue)->sum($amountColumn),
            (int) (clone $period)->count(),
        ];
    }

    /** @return array<string, array<string, array<int, mixed>>> */
    protected function emptyChart(): array
    {
        return [
            'monthly' => ['labels' => [], 'amounts' => [], 'counts' => []],
            'weekly' => ['labels' => [], 'amounts' => [], 'counts' => []],
            'daily' => ['labels' => [], 'amounts' => [], 'counts' => []],
        ];
    }

    /** @return array<string, int|float> */
    protected function emptyStats(): array
    {
        return [
            'total_count' => 0,
            'total_amount' => 0,
            'succeeded_count' => 0,
            'succeeded_amount' => 0,
            'failed_count' => 0,
            'failed_amount' => 0,
            'pending_count' => 0,
            'pending_amount' => 0,
        ];
    }
}
