<?php

namespace App\Services\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyArticleService
{
    public function shouldUseLegacy(): bool
    {
        return $this->legacyCatalogCount() > 0
            && $this->legacyCatalogCount() >= $this->laravelCatalogCount();
    }

    public function legacyCatalogCount(): int
    {
        if (! Schema::hasTable('actualites')) {
            return 0;
        }

        return (int) DB::table('actualites')->count();
    }

    public function laravelCatalogCount(): int
    {
        if (! Schema::hasTable('articles')) {
            return 0;
        }

        return (int) Article::query()->count();
    }

    /** @return list<array<string, mixed>> */
    public function pendingForAdmin(int $limit = 50): array
    {
        if (! Schema::hasTable('actualites')) {
            return [];
        }

        return DB::table('actualites as a')
            ->leftJoin('users as u', 'a.id_redaction', '=', 'u.id')
            ->where('a.statut_validation', 'en_attente')
            ->orderByDesc('a.id')
            ->limit($limit)
            ->get([
                'a.id',
                'a.titre as title',
                'a.cover',
                'a.categorie as category',
                'a.alaune',
                'a.statut_validation as validation_status',
                'a.date_add as created_at',
                'u.nom as author_nom',
                'u.mail as author_mail',
                'u.cover as author_cover',
            ])
            ->map(fn (object $row) => $this->normalizeRow($row))
            ->all();
    }

    /** @return list<array<string, mixed>> */
    public function notificationsForUser(int $userId, int $limit = 20): array
    {
        if (! Schema::hasTable('actualites')) {
            return [];
        }

        return DB::table('actualites as a')
            ->where(function ($query) use ($userId): void {
                $query->where('a.user_id', $userId)
                    ->orWhere('a.id_redaction', $userId);
            })
            ->whereIn('a.statut_validation', ['en_attente', 'rejete'])
            ->orderByDesc('a.id')
            ->limit($limit)
            ->get([
                'a.id',
                'a.titre as title',
                'a.cover',
                'a.statut_validation as validation_status',
                'a.date_add as created_at',
            ])
            ->map(fn (object $row) => $this->normalizeRow($row))
            ->all();
    }

    /** @return list<array<string, mixed>> */
    public function allForAdmin(): array
    {
        if (! Schema::hasTable('actualites')) {
            return [];
        }

        $hasBlocks = Schema::hasTable('block_news');
        $blocksCountSql = $hasBlocks
            ? '(SELECT COUNT(*) FROM block_news bn WHERE bn.id_news = a.id)'
            : '0';

        return DB::table('actualites as a')
            ->leftJoin('users as u', 'a.id_redaction', '=', 'u.id')
            ->select([
                'a.id',
                'a.titre as title',
                'a.cover',
                'a.categorie as category',
                'a.alaune',
                'a.statut_validation as validation_status',
                'a.statut_paiement as payment_status',
                'a.status',
                'a.is_paid',
                'a.price',
                'a.vues as views',
                'a.date_add as created_at',
                'u.nom as author_nom',
                'u.mail as author_mail',
                'u.cover as author_cover',
                DB::raw("{$blocksCountSql} as blocks_count"),
            ])
            ->orderByDesc('a.id')
            ->get()
            ->map(fn (object $row) => $this->normalizeFullRow($row))
            ->all();
    }

    /** @return array<string, mixed> */
    protected function normalizeFullRow(object $row): array
    {
        return array_merge($this->normalizeRow($row), [
            'payment_status' => $row->payment_status ?? 'en_attente',
            'is_published' => (int) ($row->status ?? 0) === 1,
            'is_paid' => (bool) ($row->is_paid ?? false),
            'price' => $row->price ?? null,
            'views' => (int) ($row->views ?? 0),
            'blocks_count' => (int) ($row->blocks_count ?? 0),
        ]);
    }

    public function deleteArticle(int $id): bool
    {
        if (! Schema::hasTable('actualites')) {
            return false;
        }

        $deleted = DB::table('actualites')->where('id', $id)->delete();

        if ($deleted && Schema::hasTable('block_news')) {
            DB::table('block_news')->where('id_news', $id)->delete();
        }

        return (bool) $deleted;
    }

    /** @return array{success: bool, message: string, deleted: int, errors: int} */
    public function deleteMultipleArticles(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return [
                'success' => false,
                'message' => 'IDs invalides',
                'deleted' => 0,
                'errors' => 0,
            ];
        }

        $deleted = 0;
        $errors = 0;

        foreach ($ids as $id) {
            if ($this->deleteArticle($id)) {
                $deleted++;
            } else {
                $errors++;
            }
        }

        return [
            'success' => $errors === 0,
            'message' => "{$deleted} article(s) supprimé(s)".($errors ? ", {$errors} erreur(s)" : ''),
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }

    /** @return array{success: bool, message: string} */
    public function approveArticle(int $id): array
    {
        if (! Schema::hasTable('actualites')) {
            return ['success' => false, 'message' => 'Table actualites introuvable'];
        }

        $article = DB::table('actualites')->where('id', $id)->first(['id', 'statut_validation']);

        if (! $article) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }

        if ($article->statut_validation !== 'en_attente') {
            return ['success' => false, 'message' => 'Cet article n\'est plus en attente'];
        }

        DB::table('actualites')->where('id', $id)->update([
            'statut_validation' => 'valide',
            'status' => 1,
        ]);

        return ['success' => true, 'message' => 'Article validé et publié'];
    }

    /** @return array{success: bool, message: string} */
    public function rejectArticle(int $id, string $reason): array
    {
        if (! Schema::hasTable('actualites')) {
            return ['success' => false, 'message' => 'Table actualites introuvable'];
        }

        $article = DB::table('actualites')->where('id', $id)->first(['id', 'statut_validation']);

        if (! $article) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }

        if ($article->statut_validation !== 'en_attente') {
            return ['success' => false, 'message' => 'Cet article n\'est plus en attente'];
        }

        DB::table('actualites')->where('id', $id)->update([
            'statut_validation' => 'rejete',
            'status' => 0,
        ]);

        return ['success' => true, 'message' => 'Article rejeté'];
    }

    /** @return list<array<string, mixed>> */
    public function articlesForUser(User $user, bool $asSuperAdmin = false): array
    {
        if (! Schema::hasTable('actualites')) {
            return [];
        }

        $hasBlocks = Schema::hasTable('block_news');
        $blocksCountSql = $hasBlocks
            ? '(SELECT COUNT(*) FROM block_news bn WHERE bn.id_news = a.id)'
            : '0';

        $query = DB::table('actualites as a')
            ->leftJoin('users as u', 'a.id_redaction', '=', 'u.id')
            ->select([
                'a.id',
                'a.titre as title',
                'a.cover',
                'a.categorie as category',
                'a.alaune',
                'a.statut_validation as validation_status',
                'a.statut_paiement as payment_status',
                'a.status',
                'a.is_paid',
                'a.price',
                'a.vues as views',
                'a.date_add as created_at',
                'u.nom as author_nom',
                DB::raw("{$blocksCountSql} as blocks_count"),
            ])
            ->orderByDesc('a.id');

        if (! $asSuperAdmin) {
            $query->where('a.id_redaction', $user->id);
        }

        return $query->get()
            ->map(fn (object $row) => $this->normalizeFullRow($row))
            ->all();
    }

    public function pendingCountForUser(int $userId): int
    {
        if (! Schema::hasTable('actualites')) {
            return 0;
        }

        return (int) DB::table('actualites')
            ->where('id_redaction', $userId)
            ->where('statut_validation', 'en_attente')
            ->count();
    }

    public function userCanAccess(int $articleId, User $user, bool $asSuperAdmin = false): bool
    {
        if ($asSuperAdmin) {
            return $this->legacyArticleExists($articleId);
        }

        if (! Schema::hasTable('actualites')) {
            return false;
        }

        return DB::table('actualites')
            ->where('id', $articleId)
            ->where('id_redaction', $user->id)
            ->exists();
    }

    /** @return array{success: bool, message: string, article_id?: int, requires_payment?: bool} */
    public function createArticle(User $user, array $data): array
    {
        if (! Schema::hasTable('actualites')) {
            return ['success' => false, 'message' => 'Table actualites introuvable'];
        }

        $publishesFree = $user->role?->publishesForFree() ?? false;
        $autoValidated = $user->role?->autoValidatesArticles() ?? false;

        $statutPaiement = $publishesFree ? 'gratuit' : 'en_attente';
        $statutValidation = $autoValidated ? 'valide' : 'en_attente';
        $status = $autoValidated ? 1 : 0;
        $numArticle = 'ART'.time().random_int(1000, 9999);

        $cover = $data['cover'] ?? '';
        $videos = $data['videos'] ?? '';
        $isPaid = ! empty($data['is_paid']) ? 1 : 0;
        $price = ($isPaid && ! empty($data['price'])) ? $data['price'] : null;
        $dateAdd = ! empty($data['published_at']) ? $data['published_at'] : now()->format('Y-m-d H:i:s');
        $alaune = ! empty($data['is_featured']) ? 'YES' : 'NO';
        $typePost = ! empty($videos) ? 'video' : 'article';

        $articleId = DB::table('actualites')->insertGetId([
            'id_redaction' => $user->id,
            'titre' => strip_tags($data['title']),
            'contenu' => $data['content'] ?? '',
            'cover' => $cover,
            'legende' => isset($data['caption']) ? strip_tags($data['caption']) : '',
            'videos' => $videos,
            'categorie' => strip_tags($data['category']),
            'statut_paiement' => $statutPaiement,
            'statut_validation' => $statutValidation,
            'status' => $status,
            'date_add' => $dateAdd,
            'num_article' => $numArticle,
            'type_post' => $typePost,
            'alaune' => $alaune,
            'vues' => 0,
            'is_paid' => $isPaid,
            'price' => $price,
        ]);

        $message = match (true) {
            $autoValidated => 'Article publié avec succès',
            $publishesFree => 'Article créé avec succès. En attente de validation.',
            default => 'Article créé. Veuillez procéder au paiement.',
        };

        return [
            'success' => true,
            'message' => $message,
            'article_id' => $articleId,
            'requires_payment' => ! $publishesFree,
        ];
    }

    /** @return array{success: bool, message: string, requires_payment?: bool} */
    public function updateArticle(User $user, int $id, array $data, bool $asSuperAdmin = false): array
    {
        if (! Schema::hasTable('actualites')) {
            return ['success' => false, 'message' => 'Table actualites introuvable'];
        }

        $article = DB::table('actualites')->where('id', $id)->first();

        if (! $article) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }

        if (! $asSuperAdmin && (int) $article->id_redaction !== $user->id) {
            return ['success' => false, 'message' => 'Article non trouvé ou accès refusé'];
        }

        $newStatutValidation = null;
        if (! $asSuperAdmin
            && $article->statut_paiement === 'paye'
            && in_array($article->statut_validation, ['valide', 'rejete'], true)) {
            $newStatutValidation = 'en_attente';
        }

        $videos = $data['videos'] ?? $article->videos;
        $isPaid = array_key_exists('is_paid', $data)
            ? (! empty($data['is_paid']) ? 1 : 0)
            : (int) ($article->is_paid ?? 0);

        if (array_key_exists('is_paid', $data)) {
            $price = ($isPaid && ! empty($data['price'])) ? $data['price'] : null;
        } elseif ($isPaid && array_key_exists('price', $data)) {
            $price = ! empty($data['price']) ? $data['price'] : $article->price;
        } else {
            $price = $isPaid ? $article->price : null;
        }

        $update = [
            'titre' => strip_tags($data['title'] ?? $article->titre),
            'contenu' => $data['content'] ?? $article->contenu,
            'cover' => $data['cover'] ?? $article->cover,
            'legende' => isset($data['caption']) ? strip_tags($data['caption']) : ($article->legende ?? ''),
            'videos' => $videos,
            'alaune' => ! empty($data['is_featured']) ? 'YES' : (array_key_exists('is_featured', $data) ? 'NO' : ($article->alaune ?? 'NO')),
            'categorie' => strip_tags($data['category'] ?? $article->categorie),
            'type_post' => ! empty($videos) ? 'video' : ($article->type_post ?? 'article'),
            'date_add' => $data['published_at'] ?? $article->date_add,
            'is_paid' => $isPaid,
            'price' => $price,
            'id_modif' => $user->id,
            'date_update' => now(),
        ];

        if ($newStatutValidation) {
            $update['statut_validation'] = $newStatutValidation;
        }

        DB::table('actualites')->where('id', $id)->update($update);

        return [
            'success' => true,
            'message' => 'Article mis à jour avec succès',
            'requires_payment' => ($article->statut_paiement ?? '') === 'en_attente',
        ];
    }

    /** @return array{success: bool, message: string, block_id?: int} */
    public function addBlock(int $articleId, array $data): array
    {
        if (! Schema::hasTable('block_news')) {
            return ['success' => false, 'message' => 'Table block_news introuvable'];
        }

        $numBlock = 'BLK-'.time().random_int(1000, 9999);
        $content = $this->cleanBlockContent($data['content'] ?? '');

        $blockId = DB::table('block_news')->insertGetId([
            'titre' => $data['title'] ?? null,
            'contenu' => $content,
            'id_news' => $articleId,
            'cover' => $data['cover'] ?? null,
            'legende' => $data['caption'] ?? null,
            'type_post' => $data['post_type'] ?? 'mixte',
            'videos' => $data['videos'] ?? null,
            'status' => 1,
            'num_block' => $numBlock,
        ]);

        return [
            'success' => true,
            'message' => 'Bloc ajouté avec succès',
            'block_id' => $blockId,
        ];
    }

    /** @return array{success: bool, message: string} */
    public function updateBlock(int $blockId, array $data): array
    {
        if (! Schema::hasTable('block_news')) {
            return ['success' => false, 'message' => 'Table block_news introuvable'];
        }

        if (! DB::table('block_news')->where('id', $blockId)->exists()) {
            return ['success' => false, 'message' => 'Bloc non trouvé'];
        }

        $update = [];
        foreach (['title' => 'titre', 'cover' => 'cover', 'caption' => 'legende', 'videos' => 'videos', 'post_type' => 'type_post'] as $key => $column) {
            if (array_key_exists($key, $data)) {
                $update[$column] = $data[$key];
            }
        }
        if (array_key_exists('content', $data)) {
            $update['contenu'] = $this->cleanBlockContent($data['content'] ?? '');
        }

        DB::table('block_news')->where('id', $blockId)->update($update);

        return ['success' => true, 'message' => 'Bloc mis à jour avec succès'];
    }

    public function deleteBlock(int $blockId): bool
    {
        if (! Schema::hasTable('block_news')) {
            return false;
        }

        return (bool) DB::table('block_news')->where('id', $blockId)->delete();
    }

    public function blockBelongsToArticle(int $blockId, int $articleId): bool
    {
        if (! Schema::hasTable('block_news')) {
            return false;
        }

        return DB::table('block_news')
            ->where('id', $blockId)
            ->where('id_news', $articleId)
            ->exists();
    }

    protected function legacyArticleExists(int $id): bool
    {
        return Schema::hasTable('actualites')
            && DB::table('actualites')->where('id', $id)->exists();
    }

    protected function cleanBlockContent(?string $content): string
    {
        if ($content === null || $content === '') {
            return '';
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content) ?? '';
    }

    /** @return array<string, mixed>|null */
    public function findForAdmin(int $id): ?array
    {
        if (! Schema::hasTable('actualites')) {
            return null;
        }

        $row = DB::table('actualites as a')
            ->leftJoin('users as u', 'a.id_redaction', '=', 'u.id')
            ->where('a.id', $id)
            ->first([
                'a.id',
                'a.titre as title',
                'a.contenu as content',
                'a.cover',
                'a.legende as caption',
                'a.videos',
                'a.categorie as category',
                'a.alaune',
                'a.statut_validation as validation_status',
                'a.statut_paiement as payment_status',
                'a.status',
                'a.is_paid',
                'a.price',
                'a.vues as views',
                'a.date_add as created_at',
                'u.nom as author_nom',
                'u.mail as author_mail',
                'u.cover as author_cover',
            ]);

        if (! $row) {
            return null;
        }

        return [
            'id' => (int) $row->id,
            'title' => (string) ($row->title ?? ''),
            'content' => $row->content ?? '',
            'cover' => $row->cover ?? null,
            'caption' => $row->caption ?? null,
            'videos' => $row->videos ?? null,
            'category' => $row->category ?? null,
            'alaune' => $row->alaune ?? 'NO',
            'is_featured' => ($row->alaune ?? 'NO') === 'YES',
            'validation_status' => $row->validation_status ?? 'en_attente',
            'payment_status' => $row->payment_status ?? 'en_attente',
            'is_published' => (int) ($row->status ?? 0) === 1,
            'is_paid' => (bool) ($row->is_paid ?? false),
            'price' => $row->price ?? null,
            'views' => (int) ($row->views ?? 0),
            'created_at' => $row->created_at,
            'published_at' => $row->created_at,
            'author' => [
                'nom' => $row->author_nom ?? 'Inconnu',
                'mail' => $row->author_mail ?? '',
                'cover' => $row->author_cover ?? null,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function blocksForArticle(int $articleId): array
    {
        if (! Schema::hasTable('block_news')) {
            return [];
        }

        return DB::table('block_news')
            ->where('id_news', $articleId)
            ->orderBy('id')
            ->get()
            ->map(fn (object $block): array => [
                'id' => (int) $block->id,
                'title' => $block->titre ?? null,
                'content' => $block->contenu ?? null,
                'cover' => $block->cover ?? null,
                'caption' => $block->legende ?? null,
                'videos' => $block->videos ?? null,
            ])
            ->all();
    }

    /** @return array<string, mixed> */
    protected function normalizeRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'title' => (string) ($row->title ?? ''),
            'cover' => $row->cover ?? null,
            'category' => $row->category ?? null,
            'alaune' => $row->alaune ?? 'NO',
            'validation_status' => $row->validation_status ?? 'en_attente',
            'created_at' => $row->created_at,
            'author' => isset($row->author_nom) ? [
                'nom' => $row->author_nom,
                'mail' => $row->author_mail ?? null,
                'cover' => $row->author_cover ?? null,
            ] : null,
        ];
    }
}
