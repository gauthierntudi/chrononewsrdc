<?php

namespace App\Models;

use App\Enums\ArticlePaymentStatus;
use App\Enums\ValidationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $fillable = [
        'user_id',
        'updated_by',
        'title',
        'content',
        'cover',
        'caption',
        'videos',
        'category',
        'payment_status',
        'validation_status',
        'is_published',
        'article_number',
        'post_type',
        'is_featured',
        'views',
        'is_premium',
        'price',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_status' => ArticlePaymentStatus::class,
            'validation_status' => ValidationStatus::class,
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_premium' => 'boolean',
            'price' => 'decimal:2',
            'published_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Article $article): void {
            if (empty($article->article_number)) {
                $article->article_number = 'ART'.time().random_int(1000, 9999);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ArticleBlock::class)->orderBy('sort_order');
    }

    public function tags(): HasOne
    {
        return $this->hasOne(ArticleTag::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ArticlePurchase::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where('validation_status', ValidationStatus::Approved)
            ->whereIn('payment_status', [ArticlePaymentStatus::Paid, ArticlePaymentStatus::Free]);
    }

    public function slug(): string
    {
        return Str::slug($this->title) ?: 'article';
    }

    public function routeUrl(): string
    {
        return route('articles.show', ['article' => $this->id, 'slug' => $this->slug()]);
    }

    public function coverImages(): array
    {
        if (blank($this->cover)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->cover))));
    }
}
