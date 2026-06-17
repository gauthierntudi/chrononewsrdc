<?php

namespace App\Services\Advertisement;

use App\Models\Advertisement;
use App\Models\HomeVideo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FrontAdvertisementService
{
    /** @var list<string> */
    public const FORMATS = [
        'rectangle',
        'portrait',
        'large_portrait',
        'large_rectangle',
        'paysage_small',
        'paysage_medium',
        'paysage_large',
        'video-outstream',
    ];

    /**
     * @param  list<int>  $excludeIds
     * @return array<string, mixed>|null
     */
    public function pick(string $format, ?string $emplacement, array $excludeIds = []): ?array
    {
        $candidates = $this->activeCandidates($format, $emplacement);

        if ($candidates->isEmpty()) {
            return null;
        }

        $first = $candidates->first();

        if ((int) ($first['is_locked'] ?? 0) === 1) {
            return $first;
        }

        $filtered = $candidates->filter(
            fn (array $row): bool => ! in_array((int) $row['id'], $excludeIds, true)
        )->values();

        if ($filtered->isNotEmpty()) {
            return $filtered->first();
        }

        return $first;
    }

    /** @return array<string, mixed>|null */
    public function pickVideoOutstream(): ?array
    {
        $video = HomeVideo::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if ($video === null) {
            return null;
        }

        return [
            'id' => (int) $video->id,
            'title' => (string) $video->title,
            'img' => 'https://www.youtube.com/watch?v='.$video->youtube_id,
            'url' => $video->website_url ?: '#',
            'format' => 'video-outstream',
        ];
    }

    public function track(int $adId, string $event): void
    {
        if (! Schema::hasTable('publicites')) {
            return;
        }

        if ($event === 'view') {
            DB::table('publicites')->where('id', $adId)->increment('impressions');
            if (Schema::hasColumn('publicites', 'vues')) {
                DB::table('publicites')->where('id', $adId)->increment('vues');
            }

            return;
        }

        if ($event === 'click') {
            $column = Schema::hasColumn('publicites', 'clics') ? 'clics' : 'clicks';
            DB::table('publicites')->where('id', $adId)->increment($column);
        }
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function activeCandidates(string $format, ?string $emplacement): Collection
    {
        if (! Schema::hasTable('publicites')) {
            return $this->activeCandidatesModern($format, $emplacement);
        }

        $today = now()->toDateString();

        if ($emplacement) {
            $locked = DB::table('publicites')
                ->where('statut_validation', 'valide')
                ->where('statut_diffusion', 'active')
                ->whereDate('date_debut', '<=', $today)
                ->whereDate('date_fin', '>=', $today)
                ->where('emplacement', $emplacement)
                ->where('is_locked', 1)
                ->limit(1)
                ->get();

            if ($locked->isNotEmpty()) {
                return $locked->map(fn ($row) => (array) $row);
            }
        }

        $query = DB::table('publicites')
            ->where('statut_validation', 'valide')
            ->where('statut_diffusion', 'active')
            ->whereDate('date_debut', '<=', $today)
            ->whereDate('date_fin', '>=', $today)
            ->where('format', $format);

        if ($emplacement) {
            $query->where('emplacement', $emplacement);
        }

        return $query->inRandomOrder()->get()->map(fn ($row) => (array) $row);
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function activeCandidatesModern(string $format, ?string $placement): Collection
    {
        if (! Schema::hasTable('advertisements')) {
            return collect();
        }

        $query = Advertisement::query()->active()->where('format', $format);

        if ($placement) {
            $locked = (clone $query)
                ->where('placement', $placement)
                ->where('is_locked', true)
                ->first();

            if ($locked !== null) {
                return collect([$this->modernRow($locked)]);
            }

            $query->where('placement', $placement);
        }

        return $query->inRandomOrder()->get()->map(fn (Advertisement $ad) => $this->modernRow($ad));
    }

    /** @return array<string, mixed> */
    protected function modernRow(Advertisement $ad): array
    {
        return [
            'id' => (int) $ad->id,
            'titre' => $ad->title,
            'image_url' => $ad->image_url,
            'url_cible' => $ad->target_url,
            'format' => $ad->format,
            'is_locked' => $ad->is_locked ? 1 : 0,
        ];
    }
}
