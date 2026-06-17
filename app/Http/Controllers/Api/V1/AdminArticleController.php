<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ValidationStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Article\LegacyArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminArticleController extends Controller
{
    public function pending(Request $request, LegacyArticleService $legacy): JsonResponse
    {
        if ($legacy->shouldUseLegacy()) {
            return response()->json([
                'success' => true,
                'articles' => $legacy->pendingForAdmin(),
            ]);
        }

        $articles = Article::query()
            ->with('author:id,nom,mail')
            ->where('validation_status', ValidationStatus::Pending)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'articles' => $articles,
        ]);
    }

    public function approve(Request $request, int $articleId, LegacyArticleService $legacy): JsonResponse
    {
        if ($legacy->shouldUseLegacy()) {
            $result = $legacy->approveArticle($articleId);

            return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
        }

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('validate', $article);

        $article->update([
            'validation_status' => ValidationStatus::Approved,
            'is_published' => true,
            'published_at' => $article->published_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Article approuvé',
            'article' => $article->fresh(),
        ]);
    }

    public function reject(Request $request, int $articleId, LegacyArticleService $legacy): JsonResponse
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:2000'],
        ]);

        if ($legacy->shouldUseLegacy()) {
            $result = $legacy->rejectArticle($articleId, $data['reject_reason']);

            return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
        }

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('validate', $article);

        $article->update([
            'validation_status' => ValidationStatus::Rejected,
            'is_published' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Article rejeté',
            'article' => $article->fresh(),
        ]);
    }

    public function index(Request $request, LegacyArticleService $legacy): JsonResponse
    {
        if ($legacy->shouldUseLegacy()) {
            $articles = $legacy->allForAdmin();

            return response()->json([
                'success' => true,
                'articles' => $articles,
                'total' => count($articles),
            ]);
        }

        $articles = Article::query()
            ->with(['author:id,nom,mail,cover'])
            ->withCount('blocks')
            ->latest()
            ->paginate(min((int) $request->query('limit', 50), 100));

        return response()->json([
            'success' => true,
            'articles' => $articles,
        ]);
    }

    public function show(int $articleId, LegacyArticleService $legacy): JsonResponse
    {
        if ($legacy->shouldUseLegacy()) {
            $article = $legacy->findForAdmin($articleId);

            if (! $article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'article' => $article,
            ]);
        }

        $article = Article::query()
            ->with('author:id,nom,mail,cover')
            ->findOrFail($articleId);

        return response()->json([
            'success' => true,
            'article' => $article,
        ]);
    }

    public function blocks(int $articleId, LegacyArticleService $legacy): JsonResponse
    {
        if ($legacy->shouldUseLegacy()) {
            return response()->json([
                'success' => true,
                'blocks' => $legacy->blocksForArticle($articleId),
            ]);
        }

        $article = Article::query()->findOrFail($articleId);

        return response()->json([
            'success' => true,
            'blocks' => $article->blocks()->get(),
        ]);
    }

    public function destroyAdmin(int $articleId, LegacyArticleService $legacy): JsonResponse
    {
        if ($legacy->shouldUseLegacy()) {
            if (! $legacy->deleteArticle($articleId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Article supprimé avec succès',
            ]);
        }

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('delete', $article);
        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article supprimé avec succès',
        ]);
    }

    public function destroyMultiple(Request $request, LegacyArticleService $legacy): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        if ($legacy->shouldUseLegacy()) {
            $result = $legacy->deleteMultipleArticles($data['ids']);

            return response()->json($result, (($result['deleted'] ?? 0) > 0) ? 200 : 422);
        }

        $deleted = 0;
        $errors = 0;

        foreach ($data['ids'] as $id) {
            $article = Article::query()->find($id);

            if (! $article) {
                $errors++;

                continue;
            }

            try {
                $this->authorize('delete', $article);
                $article->delete();
                $deleted++;
            } catch (\Throwable) {
                $errors++;
            }
        }

        return response()->json([
            'success' => $errors === 0,
            'message' => "{$deleted} article(s) supprimé(s)".($errors ? ", {$errors} erreur(s)" : ''),
            'deleted' => $deleted,
            'errors' => $errors,
        ], $deleted > 0 ? 200 : 422);
    }
}
