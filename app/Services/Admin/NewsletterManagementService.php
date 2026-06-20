<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class NewsletterManagementService
{
    private const TABLE = 'newsletter_subscribers';

    public function paginate(
        int $page = 1,
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null,
    ): LengthAwarePaginator {
        if (! Schema::hasTable(self::TABLE)) {
            return new Paginator([], 0, min(max($perPage, 1), 50), max($page, 1));
        }

        $query = DB::table(self::TABLE);

        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('email', 'like', $term);

                if (Schema::hasColumn(self::TABLE, 'source')) {
                    $builder->orWhere('source', 'like', $term);
                }
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $paginator = $query
            ->orderByDesc('id')
            ->paginate(
                perPage: min(max($perPage, 1), 50),
                page: max($page, 1),
            );

        $items = collect($paginator->items())
            ->map(fn (object $row) => $this->normalizeRow($row))
            ->all();

        return new Paginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => Paginator::resolveCurrentPath()],
        );
    }

    /** @return array{total: int, active: int, inactive: int, this_week: int, this_month: int, active_rate: float} */
    public function stats(): array
    {
        if (! Schema::hasTable(self::TABLE)) {
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'this_week' => 0,
                'this_month' => 0,
                'active_rate' => 0.0,
            ];
        }

        $total = (int) DB::table(self::TABLE)->count();
        $active = (int) DB::table(self::TABLE)->where('status', 'active')->count();
        $inactive = max(0, $total - $active);

        $thisWeek = 0;
        $thisMonth = 0;
        if (Schema::hasColumn(self::TABLE, 'created_at')) {
            $thisWeek = (int) DB::table(self::TABLE)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count();
            $thisMonth = (int) DB::table(self::TABLE)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();
        }

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'active_rate' => $total > 0 ? round(($active / $total) * 100, 1) : 0.0,
        ];
    }

    public function toggleStatus(int $subscriberId): array
    {
        $row = $this->findRow($subscriberId);
        $newStatus = ($row->status ?? '') === 'active' ? 'inactive' : 'active';

        $updates = ['status' => $newStatus];
        if (Schema::hasColumn(self::TABLE, 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table(self::TABLE)->where('id', $subscriberId)->update($updates);

        return [
            'success' => true,
            'message' => $newStatus === 'active' ? 'Abonné réactivé.' : 'Abonné désactivé.',
            'new_status' => $newStatus,
        ];
    }

    public function delete(int $subscriberId): array
    {
        $this->findRow($subscriberId);
        DB::table(self::TABLE)->where('id', $subscriberId)->delete();

        return [
            'success' => true,
            'message' => 'Abonné supprimé.',
        ];
    }

    private function findRow(int $subscriberId): object
    {
        if (! Schema::hasTable(self::TABLE)) {
            throw ValidationException::withMessages([
                'subscriber' => ['Table newsletter indisponible.'],
            ]);
        }

        $row = DB::table(self::TABLE)->where('id', $subscriberId)->first();

        if ($row === null) {
            throw ValidationException::withMessages([
                'subscriber' => ['Abonné introuvable.'],
            ]);
        }

        return $row;
    }

    /** @return array<string, mixed> */
    private function normalizeRow(object $row): array
    {
        $ip = $row->ip_address ?? $row->ip ?? null;

        return [
            'id' => (int) $row->id,
            'email' => (string) ($row->email ?? ''),
            'status' => (string) ($row->status ?? 'inactive'),
            'consent' => (bool) ($row->consent ?? false),
            'source' => (string) ($row->source ?? ''),
            'ip_address' => $ip,
            'user_agent' => (string) ($row->user_agent ?? ''),
            'created_at' => $row->created_at ?? null,
            'updated_at' => $row->updated_at ?? null,
        ];
    }
}
