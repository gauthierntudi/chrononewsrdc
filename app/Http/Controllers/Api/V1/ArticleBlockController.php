<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleBlock;
use App\Services\Article\LegacyArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleBlockController extends Controller
{
    public function __construct(
        protected LegacyArticleService $legacy,
    ) {}

    public function index(Request $request, int $articleId): JsonResponse
    {
        if ($this->legacy->shouldUseLegacy()) {
            if (! $this->legacy->userCanAccess($articleId, $request->user(), $request->user()->isSuperAdmin())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'blocks' => $this->legacy->blocksForArticle($articleId),
            ]);
        }

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('view', $article);

        return response()->json([
            'success' => true,
            'blocks' => $article->blocks()->get(),
        ]);
    }

    public function store(Request $request, int $articleId): JsonResponse
    {
        $data = $this->validatedBlockData($request);

        if (empty($data['title']) && empty($data['content']) && empty($data['cover']) && empty($data['videos'])) {
            return response()->json([
                'success' => false,
                'message' => 'Au moins un champ doit être renseigné',
            ], 422);
        }

        if ($this->legacy->shouldUseLegacy()) {
            if (! $this->legacy->userCanAccess($articleId, $request->user(), $request->user()->isSuperAdmin())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé',
                ], 404);
            }

            $result = $this->legacy->addBlock($articleId, $data);

            return response()->json($result, ($result['success'] ?? false) ? 201 : 422);
        }

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('update', $article);

        $sortOrder = (int) $article->blocks()->max('sort_order') + 1;

        $block = $article->blocks()->create([
            ...$data,
            'content' => $this->cleanContent($data['content'] ?? ''),
            'is_active' => true,
            'sort_order' => $sortOrder,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bloc ajouté avec succès',
            'block' => $block,
            'block_id' => $block->id,
        ], 201);
    }

    public function update(Request $request, int $blockId): JsonResponse
    {
        $data = $this->validatedBlockData($request, true);

        if ($this->legacy->shouldUseLegacy()) {
            if (! $this->legacyBlockAccessible($blockId, $request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bloc non trouvé',
                ], 404);
            }

            $result = $this->legacy->updateBlock($blockId, $data);

            return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
        }

        $block = ArticleBlock::query()->findOrFail($blockId);
        $this->authorize('update', $block->article);

        if (array_key_exists('content', $data)) {
            $data['content'] = $this->cleanContent($data['content'] ?? '');
        }

        $block->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Bloc mis à jour avec succès',
            'block' => $block->fresh(),
        ]);
    }

    public function destroy(Request $request, int $blockId): JsonResponse
    {
        if ($this->legacy->shouldUseLegacy()) {
            if (! $this->legacyBlockAccessible($blockId, $request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bloc non trouvé',
                ], 404);
            }

            $this->legacy->deleteBlock($blockId);

            return response()->json([
                'success' => true,
                'message' => 'Bloc supprimé avec succès',
            ]);
        }

        $block = ArticleBlock::query()->findOrFail($blockId);
        $this->authorize('update', $block->article);
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bloc supprimé avec succès',
        ]);
    }

    protected function legacyBlockAccessible(int $blockId, Request $request): bool
    {
        $articleId = (int) \Illuminate\Support\Facades\DB::table('block_news')
            ->where('id', $blockId)
            ->value('id_news');

        if (! $articleId) {
            return false;
        }

        return $this->legacy->userCanAccess($articleId, $request->user(), $request->user()->isSuperAdmin());
    }

    /** @return array<string, mixed> */
    protected function validatedBlockData(Request $request, bool $partial = false): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'cover' => ['nullable', 'string'],
            'caption' => ['nullable', 'string', 'max:500'],
            'videos' => ['nullable', 'string'],
            'post_type' => ['nullable', 'string', 'max:50'],
        ]);

        return $data;
    }

    protected function cleanContent(?string $content): string
    {
        if ($content === null || $content === '') {
            return '';
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content) ?? '';
    }
}
