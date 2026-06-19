<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(int $article, ?string $slug = null): View|RedirectResponse
    {
        $row = DB::table('actualites')
            ->where('id', $article)
            ->where('status', 1)
            ->where('statut_validation', 'valide')
            ->whereIn('statut_paiement', ['paye', 'gratuit'])
            ->first(['id', 'titre', 'categorie']);

        if ($row === null) {
            abort(404);
        }

        $repoRoot = \App\Support\ProjectPaths::root();
        require_once $repoRoot.'/includes/legacy-helpers.php';

        $canonicalSlug = slugify(clean_title($row->titre));

        if ($slug !== null && $slug !== '' && $slug !== $canonicalSlug) {
            return redirect()->route('articles.show', [
                'article' => $article,
                'slug' => $canonicalSlug,
            ], 301);
        }

        return view('pages.article.show', [
            'article_id' => $article,
            'articleTitle' => clean_title($row->titre),
            'legacyHeader' => [
                'currentArticle' => [
                    'id' => (int) $row->id,
                    'titre' => $row->titre,
                    'categorie' => $row->categorie,
                ],
            ],
            'legacyHead' => [
                'frontStylesheet' => '/css/styles-view.css',
            ],
        ]);
    }
}
