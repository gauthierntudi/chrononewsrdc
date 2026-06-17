<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        return view('pages.search.index', [
            'q' => $q,
            'page' => $page,
            'legacyHead' => [
                'frontExtraStylesheets' => [
                    '/css/category-animations.css',
                    '/css/category-pagination.css',
                ],
            ],
        ]);
    }
}
