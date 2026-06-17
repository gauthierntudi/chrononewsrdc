<?php

namespace App\Services\Article;

use App\Models\Article;
use App\Support\FrontHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicArticleService
{
    public function __construct(
        private readonly LegacyArticleService $legacyArticles,
    ) {}

    public function usesLegacyCatalog(): bool
    {
        return $this->legacyArticles->shouldUseLegacy();
    }

    /** @return array<string, mixed>|null */
    public function findPublished(int $id): ?array
    {
        if ($this->usesLegacyCatalog()) {
            return $this->findPublishedLegacy($id);
        }

        $article = Article::query()->published()->with('author')->find($id);

        return $article ? $this->normalizeEloquentArticle($article) : null;
    }

    /** @return list<array<string, mixed>> */
    public function blocksFor(int $articleId): array
    {
        if ($this->usesLegacyCatalog()) {
            return $this->legacyArticles->blocksForArticle($articleId);
        }

        $article = Article::query()->with('blocks')->find($articleId);

        if (! $article) {
            return [];
        }

        return $article->blocks->map(fn ($block): array => [
            'id' => $block->id,
            'title' => $block->title,
            'content' => $block->content,
            'cover' => $block->cover,
            'caption' => $block->caption,
            'videos' => $block->videos,
        ])->all();
    }

    public function incrementViews(int $articleId, int $currentViews): int
    {
        $next = $currentViews + 1;

        if ($this->usesLegacyCatalog() && Schema::hasTable('actualites')) {
            DB::table('actualites')->where('id', $articleId)->update(['vues' => $next]);

            return $next;
        }

        Article::query()->whereKey($articleId)->update(['views' => $next]);

        return $next;
    }

    /** @return array<string, mixed>|null */
    public function homeHero(): ?array
    {
        if (! $this->usesLegacyCatalog()) {
            $article = Article::query()->published()->where('is_featured', true)->inRandomOrder()->first();

            return $article ? $this->normalizeEloquentArticle($article) : null;
        }

        $row = $this->legacyPublishedQuery()
            ->where('a.alaune', 'YES')
            ->whereRaw('COALESCE(a.date_add, a.created_at) >= (NOW() - INTERVAL 45 DAY)')
            ->inRandomOrder()
            ->first();

        return $row ? $this->normalizeLegacyRow($row) : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function homeRecents(int $limit = 4, ?int $excludeId = null): array
    {
        if (! $this->usesLegacyCatalog()) {
            return Article::query()
                ->published()
                ->with('author')
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Article $a) => $this->normalizeEloquentArticle($a))
                ->all();
        }

        return $this->legacyPublishedQuery()
            ->when($excludeId, fn (Builder $q) => $q->where('a.id', '!=', $excludeId))
            ->orderByDesc(DB::raw('COALESCE(a.date_add, a.created_at)'))
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => $this->normalizeLegacyRow($row))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentByCategory(string $category, int $limit = 4): array
    {
        if (! $this->usesLegacyCatalog()) {
            return Article::query()
                ->published()
                ->with('author')
                ->where('category', $category)
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Article $a) => $this->normalizeEloquentArticle($a))
                ->all();
        }

        return $this->legacyPublishedQuery()
            ->where('a.categorie', $category)
            ->orderByDesc(DB::raw('COALESCE(a.date_add, a.created_at)'))
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => $this->normalizeLegacyRow($row))
            ->all();
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    public function paginateByCategory(string $category, int $page = 1, int $perPage = 8): LengthAwarePaginator
    {
        if (! $this->usesLegacyCatalog()) {
            $paginator = Article::query()
                ->published()
                ->with('author')
                ->where('category', $category)
                ->orderByDesc('published_at')
                ->paginate($perPage, ['*'], 'page', $page);

            return $paginator->through(fn (Article $a) => $this->normalizeEloquentArticle($a));
        }

        $total = (int) $this->legacyPublishedQuery()
            ->where('a.categorie', $category)
            ->count();

        $items = $this->legacyPublishedQuery()
            ->where('a.categorie', $category)
            ->orderByDesc('a.date_add')
            ->offset(max(0, ($page - 1) * $perPage))
            ->limit($perPage)
            ->get()
            ->map(fn (object $row) => $this->normalizeLegacyRow($row));

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => route('categories.show', ['category' => $category])]
        );
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    public function search(string $query, int $page = 1, int $perPage = 8): LengthAwarePaginator
    {
        $query = trim($query);

        if ($query === '') {
            return new Paginator(collect(), 0, $perPage, $page, ['path' => route('search')]);
        }

        if (! $this->usesLegacyCatalog()) {
            $paginator = Article::query()
                ->published()
                ->with('author')
                ->where(function ($builder) use ($query): void {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->orderByDesc('published_at')
                ->paginate($perPage, ['*'], 'page', $page);

            return $paginator->through(fn (Article $a) => $this->normalizeEloquentArticle($a));
        }

        $like = '%'.$query.'%';

        $base = $this->legacyPublishedQuery()
            ->where(function (Builder $builder) use ($like): void {
                $builder->where('a.titre', 'like', $like)
                    ->orWhere('a.contenu', 'like', $like);
            });

        $total = (int) (clone $base)->count();

        $items = (clone $base)
            ->orderByDesc('a.date_add')
            ->offset(max(0, ($page - 1) * $perPage))
            ->limit($perPage)
            ->get()
            ->map(fn (object $row) => $this->normalizeLegacyRow($row));

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => route('search'), 'query' => ['q' => $query]]
        );
    }

    /** @return list<array<string, mixed>> */
    public function sidebarRandom(int $limit = 2): array
    {
        if (! $this->usesLegacyCatalog()) {
            return Article::query()
                ->published()
                ->with('author')
                ->inRandomOrder()
                ->limit($limit)
                ->get()
                ->map(fn (Article $a) => $this->normalizeEloquentArticle($a))
                ->all();
        }

        return $this->legacyPublishedQuery()
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => $this->normalizeLegacyRow($row))
            ->all();
    }

    private function legacyPublishedQuery(): Builder
    {
        return DB::table('actualites as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.id_redaction')
            ->where('a.status', 1)
            ->where('a.statut_validation', 'valide')
            ->whereIn('a.statut_paiement', ['paye', 'gratuit'])
            ->select([
                'a.*',
                'u.nom as auteur_nom',
                'u.cover as auteur_cover',
                'u.titre as auteur_titre',
            ]);
    }

    /** @return array<string, mixed>|null */
    private function findPublishedLegacy(int $id): ?array
    {
        $row = $this->legacyPublishedQuery()
            ->where('a.id', $id)
            ->first();

        return $row ? $this->normalizeLegacyRow($row) : null;
    }

    /** @return array<string, mixed> */
    private function normalizeLegacyRow(object $row): array
    {
        $title = (string) ($row->titre ?? '');

        return [
            'id' => (int) $row->id,
            'title' => $title,
            'titre' => $title,
            'content' => $row->contenu ?? '',
            'contenu' => $row->contenu ?? '',
            'cover' => $row->cover ?? null,
            'category' => $row->categorie ?? null,
            'categorie' => $row->categorie ?? null,
            'videos' => $row->videos ?? null,
            'views' => FrontHelper::viewsInt($row->vues ?? 0),
            'vues' => $row->vues ?? 0,
            'date_add' => $row->date_add ?? null,
            'created_at' => $row->date_add ?? $row->created_at ?? null,
            'auteur_nom' => $row->auteur_nom ?? 'Rédaction',
            'author' => [
                'nom' => $row->auteur_nom ?? 'Rédaction',
                'cover' => $row->auteur_cover ?? null,
                'titre' => $row->auteur_titre ?? null,
            ],
            'is_featured' => ($row->alaune ?? 'NO') === 'YES',
            'is_premium' => (bool) ($row->is_paid ?? false),
            'price' => $row->price ?? null,
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeEloquentArticle(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'titre' => $article->title,
            'content' => $article->content ?? '',
            'contenu' => $article->content ?? '',
            'cover' => $article->cover,
            'category' => $article->category,
            'categorie' => $article->category,
            'videos' => $article->videos,
            'views' => (int) $article->views,
            'vues' => (string) $article->views,
            'date_add' => $article->published_at?->toDateTimeString(),
            'created_at' => $article->published_at?->toDateTimeString(),
            'auteur_nom' => $article->author?->name ?? 'Rédaction',
            'author' => [
                'nom' => $article->author?->name ?? 'Rédaction',
                'cover' => $article->author?->avatar ?? null,
                'titre' => $article->author?->job_title ?? null,
            ],
            'is_featured' => (bool) $article->is_featured,
            'is_premium' => (bool) $article->is_premium,
            'price' => $article->price,
        ];
    }
}
