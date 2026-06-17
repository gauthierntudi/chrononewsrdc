<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LegacyFront;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegacyFrontController extends Controller
{
    public function __construct(
        private readonly LegacyFront $legacy,
    ) {}

    public function home(): Response
    {
        return $this->legacy->render('index.php');
    }

    public function article(int $article, ?string $slug = null): Response
    {
        $query = ['article' => $article];

        if ($slug !== null && $slug !== '') {
            $query['slug'] = $slug;
        }

        return $this->legacy->render('viewer.php', $query);
    }

    public function category(string $category, ?int $page = null): Response
    {
        $query = ['category' => urldecode($category)];

        if ($page !== null && $page > 0) {
            $query['page'] = $page;
        }

        return $this->legacy->render('category.php', $query);
    }

    public function search(Request $request): Response
    {
        return $this->legacy->render('search.php', $request->query());
    }

    public function contact(): Response
    {
        return $this->legacy->render('contact.php');
    }

    public function about(): Response
    {
        return $this->legacy->render('qui-sommes-nous.php');
    }

    public function privacy(): Response
    {
        return $this->legacy->render('politique-de-confidentialite.php');
    }

    public function ogImage(Request $request): Response
    {
        return $this->legacy->render('og-image.php', $request->query());
    }
}
