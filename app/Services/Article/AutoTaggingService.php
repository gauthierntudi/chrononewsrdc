<?php

namespace App\Services\Article;

use App\Models\Article;
use App\Models\ArticleTag;

class AutoTaggingService
{
    /** @return list<string> */
    public function syncForArticle(Article $article, int $maxTags = 5): array
    {
        $existing = ArticleTag::query()->where('article_id', $article->id)->first();

        if ($existing && ! empty($existing->tags)) {
            return $existing->tags;
        }

        $tags = $this->generateFromContent($article->title, $article->content ?? '', $maxTags);

        ArticleTag::query()->updateOrCreate(
            ['article_id' => $article->id],
            ['tags' => $tags],
        );

        return $tags;
    }

    /** @return list<string> */
    public function regenerateForArticle(Article $article, int $maxTags = 5): array
    {
        $tags = $this->generateFromContent($article->title, $article->content ?? '', $maxTags);

        ArticleTag::query()->updateOrCreate(
            ['article_id' => $article->id],
            ['tags' => $tags],
        );

        return $tags;
    }

    /** @return list<string> */
    public function generateFromContent(string $title, string $content, int $maxTags = 5): array
    {
        $titleWeight = 3;
        $titleKeywords = $this->extractKeywords($title, 5);
        $contentKeywords = $this->extractKeywords(strip_tags($content), 15);

        $combined = [];
        foreach ($titleKeywords as $keyword) {
            $combined[$keyword] = ($combined[$keyword] ?? 0) + $titleWeight;
        }
        foreach ($contentKeywords as $keyword) {
            $combined[$keyword] = ($combined[$keyword] ?? 0) + 1;
        }

        arsort($combined);

        return array_map(
            'ucfirst',
            array_slice(array_keys($combined), 0, $maxTags),
        );
    }

    /** @return list<string> */
    protected function extractKeywords(string $text, int $maxKeywords = 10): array
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? '';
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $stopWords = $this->frenchStopWords();
        $filtered = array_filter($words, function (string $word) use ($stopWords): bool {
            return mb_strlen($word) >= 4
                && ! in_array($word, $stopWords, true)
                && ! is_numeric($word);
        });

        $freq = array_count_values($filtered);
        arsort($freq);

        return array_slice(array_keys($freq), 0, $maxKeywords);
    }

    /** @return list<string> */
    protected function frenchStopWords(): array
    {
        return [
            'le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'mais', 'donc', 'or', 'ni', 'car',
            'ce', 'cette', 'ces', 'mon', 'ton', 'son', 'notre', 'votre', 'leur', 'mes', 'tes', 'ses', 'nos', 'vos', 'leurs',
            'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles', 'on',
            'me', 'te', 'se', 'lui', 'leur', 'moi', 'toi', 'soi',
            'dans', 'sur', 'sous', 'avec', 'sans', 'pour', 'par', 'contre', 'vers', 'chez', 'entre',
            'qui', 'que', 'quoi', 'dont', 'où', 'si', 'plus', 'moins', 'très', 'trop', 'assez',
            'est', 'sont', 'être', 'avoir', 'fait', 'faire', 'dit', 'dire', 'peut', 'pouvoir',
            'au', 'aux', 'à', 'ne', 'pas', 'non', 'oui', 'bien', 'mal', 'tout', 'tous', 'toute', 'toutes',
            'ça', 'cela', 'ceci', 'comment', 'quand', 'pourquoi', 'aussi', 'encore', 'déjà', 'alors',
            'après', 'avant', 'depuis', 'pendant', 'comme', 'même', 'autre', 'plusieurs', 'quelques',
            'son', 'sa', 'ses', 'leur', 'leurs', 'aucun', 'aucune', 'chaque', 'certain', 'certaine',
        ];
    }
}
