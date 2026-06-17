<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class JustForYouController extends Controller
{
    public function index(?int $page = null): View
    {
        $page = max(1, $page ?? (int) request()->query('page', 1));

        return view('pages.just-for-you.index', [
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
