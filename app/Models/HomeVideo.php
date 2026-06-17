<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class HomeVideo extends Model
{
    protected $fillable = [
        'youtube_id',
        'title',
        'subtitle',
        'website_url',
        'is_active',
    ];

    public function getTable(): string
    {
        return Schema::hasTable('home_video') ? 'home_video' : 'home_videos';
    }

    public function usesTimestamps(): bool
    {
        return Schema::hasTable('home_videos');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
