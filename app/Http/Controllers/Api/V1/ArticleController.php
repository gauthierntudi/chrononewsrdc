<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ValidationStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Article\ArticleService;
use App\Services\Article\AutoTaggingService;
use App\Services\Article\LegacyArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleService $articles,
        protected AutoTaggingService $tagging,
        protected LegacyArticleService $legacy,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->legacy->shouldUseLegacy()) {
            $articles = $this->legacy->articlesForUser($user, $user->isSuperAdmin());

            return response()->json([
                'success' => true,
                'articles' => [
                    'data' => $articles,
                ],
            ]);
        }

        $query = Article::query()->with('author')->latest();

        if (! $user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'success' => true,
            'articles' => $query->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Article::class);

        $data = $this->validatedArticleData($request);

        if ($this->legacy->shouldUseLegacy()) {
            $result = $this->legacy->createArticle($request->user(), $data);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'requires_payment' => $result['requires_payment'] ?? false,
                'article_id' => $result['article_id'] ?? null,
            ], ($result['success'] ?? false) ? 201 : 422);
        }

        $result = $this->articles->create($request->user(), $data);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'requires_payment' => $result['requires_payment'],
            'article_id' => $result['article']->id,
            'article' => $result['article'],
        ], 201);
    }

    public function show(Request $request, int $articleId): JsonResponse
    {
        if ($this->legacy->shouldUseLegacy()) {
            if (! $this->legacy->userCanAccess($articleId, $request->user(), $request->user()->isSuperAdmin())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé',
                ], 404);
            }

            $article = $this->legacy->findForAdmin($articleId);

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

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('view', $article);
        $article->load(['author', 'blocks']);

        return response()->json([
            'success' => true,
            'article' => $article,
        ]);
    }

    public function update(Request $request, int $articleId): JsonResponse
    {
        $data = $this->validatedArticleData($request, true);

        if ($this->legacy->shouldUseLegacy()) {
            $result = $this->legacy->updateArticle(
                $request->user(),
                $articleId,
                $data,
                $request->user()->isSuperAdmin(),
            );

            return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
        }

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('update', $article);

        $result = $this->articles->update(
            $request->user(),
            $article,
            $data,
            $request->user()->isSuperAdmin(),
        );

        return response()->json($result);
    }

    public function destroy(Request $request, int $articleId): JsonResponse
    {
        if ($this->legacy->shouldUseLegacy()) {
            if (! $this->legacy->userCanAccess($articleId, $request->user(), $request->user()->isSuperAdmin())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé',
                ], 404);
            }

            if (! $this->legacy->deleteArticle($articleId)) {
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

    public function pendingCount(Request $request): JsonResponse
    {
        if ($this->legacy->shouldUseLegacy()) {
            return response()->json([
                'success' => true,
                'count' => $this->legacy->pendingCountForUser($request->user()->id),
            ]);
        }

        $count = Article::query()
            ->where('user_id', $request->user()->id)
            ->where('validation_status', ValidationStatus::Pending)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function tags(Request $request, int $articleId): JsonResponse
    {
        if ($this->legacy->shouldUseLegacy()) {
            return response()->json([
                'success' => true,
                'tags' => [],
            ]);
        }

        $article = Article::query()->findOrFail($articleId);
        $this->authorize('view', $article);

        $tags = $this->tagging->syncForArticle($article);

        return response()->json([
            'success' => true,
            'tags' => $tags,
        ]);
    }

    /** @return array<string, mixed> */
    protected function validatedArticleData(Request $request, bool $partial = false): array
    {
        $rules = [
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'cover' => ['nullable', 'string'],
            'caption' => ['nullable', 'string', 'max:500'],
            'videos' => ['nullable', 'string'],
            'category' => [$partial ? 'sometimes' : 'required', 'string', 'max:50'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_paid' => ['sometimes', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'has_slide' => ['sometimes', 'boolean'],
            'slide_images' => ['sometimes', 'array'],
        ];

        return $request->validate($rules);
    }
}
