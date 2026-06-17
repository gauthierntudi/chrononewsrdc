<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\Categories;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(string $category, ?int $page = null): View|RedirectResponse
    {
        $segment = urldecode($category);
        $resolved = Categories::fromSlug($segment);

        if ($resolved === null) {
            abort(404);
        }

        $page = max(1, $page ?? (int) request()->query('page', 1));
        $canonicalSlug = Categories::slug($resolved);

        if ($segment !== $canonicalSlug) {
            $params = ['category' => $canonicalSlug];
            if ($page > 1) {
                $params['page'] = $page;
            }

            return redirect()->route('categories.show', $params, 301);
        }

        return view('pages.category.show', [
            'category' => $resolved,
            'page' => $page,
            'legacyHeader' => ['currentArticle' => ['categorie' => $resolved]],
            'legacyHead' => [
                'frontExtraStylesheets' => [
                    '/css/category-animations.css',
                    '/css/category-pagination.css',
                ],
            ],
        ]);
    }
}
