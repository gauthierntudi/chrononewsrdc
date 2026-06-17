<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleBlock extends Model
{
    protected $fillable = [
        'article_id',
        'block_number',
        'title',
        'content',
        'cover',
        'caption',
        'videos',
        'post_type',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ArticleBlock $block): void {
            if (empty($block->block_number)) {
                $block->block_number = 'BLK-'.time().random_int(1000, 9999);
            }
        });
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
