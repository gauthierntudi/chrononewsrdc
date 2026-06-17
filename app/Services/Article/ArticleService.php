<?php

namespace App\Services\Article;

use App\Enums\ArticlePaymentStatus;
use App\Enums\ValidationStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Arr;

class ArticleService
{
    public function __construct(
        protected AutoTaggingService $tagging,
    ) {}

    public function create(User $user, array $data): array
    {
        $role = $user->role;
        $publishesFree = $role->publishesForFree();
        $autoValidated = $role->autoValidatesArticles();

        $videos = Arr::get($data, 'videos', '');
        $cover = Arr::get($data, 'cover', '');

        if (! empty($data['has_slide']) && ! empty($data['slide_images'])) {
            $cover = implode(',', $data['slide_images']);
        }

        $isPremium = ! empty($data['is_paid']);

        $article = Article::query()->create([
            'user_id' => $user->id,
            'title' => strip_tags($data['title']),
            'content' => $data['content'] ?? '',
            'cover' => $cover,
            'caption' => isset($data['caption']) ? strip_tags($data['caption']) : '',
            'videos' => $videos,
            'category' => strip_tags($data['category']),
            'payment_status' => $publishesFree ? ArticlePaymentStatus::Free : ArticlePaymentStatus::Pending,
            'validation_status' => $autoValidated ? ValidationStatus::Approved : ValidationStatus::Pending,
            'is_published' => $autoValidated,
            'post_type' => ! empty($videos) ? 'video' : 'article',
            'is_featured' => ($data['is_featured'] ?? false) ? true : false,
            'is_premium' => $isPremium,
            'price' => $isPremium && ! empty($data['price']) ? $data['price'] : null,
            'published_at' => ! empty($data['published_at']) ? $data['published_at'] : ($autoValidated ? now() : null),
        ]);

        $this->tagging->syncForArticle($article);

        $message = match (true) {
            $autoValidated => 'Article publié avec succès',
            $publishesFree => 'Article créé avec succès. En attente de validation.',
            default => 'Article créé. Veuillez procéder au paiement.',
        };

        return [
            'success' => true,
            'message' => $message,
            'article' => $article,
            'requires_payment' => ! $publishesFree,
        ];
    }

    public function update(User $user, Article $article, array $data, bool $asSuperAdmin = false): array
    {
        $needsRevalidation = ! $asSuperAdmin
            && $article->payment_status === ArticlePaymentStatus::Paid
            && in_array($article->validation_status, [ValidationStatus::Approved, ValidationStatus::Rejected], true);

        $videos = Arr::get($data, 'videos', $article->videos);
        $isPremium = array_key_exists('is_paid', $data)
            ? ! empty($data['is_paid'])
            : $article->is_premium;

        $article->fill([
            'title' => strip_tags($data['title'] ?? $article->title),
            'content' => $data['content'] ?? $article->content,
            'cover' => $data['cover'] ?? $article->cover,
            'caption' => isset($data['caption']) ? strip_tags($data['caption']) : $article->caption,
            'videos' => $videos,
            'category' => strip_tags($data['category'] ?? $article->category),
            'post_type' => ! empty($videos) ? 'video' : ($article->post_type ?? 'article'),
            'is_featured' => $data['is_featured'] ?? $article->is_featured,
            'is_premium' => $isPremium,
            'price' => $isPremium && ! empty($data['price']) ? $data['price'] : ($isPremium ? $article->price : null),
            'published_at' => $data['published_at'] ?? $article->published_at,
            'updated_by' => $user->id,
        ]);

        if ($needsRevalidation) {
            $article->validation_status = ValidationStatus::Pending;
        }

        $article->save();

        if (array_key_exists('title', $data) || array_key_exists('content', $data)) {
            $this->tagging->regenerateForArticle($article->fresh());
        }

        return [
            'success' => true,
            'message' => 'Article mis à jour avec succès',
            'article' => $article->fresh(),
            'requires_payment' => $article->payment_status === ArticlePaymentStatus::Pending,
        ];
    }
}
