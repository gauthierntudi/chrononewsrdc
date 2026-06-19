<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Media\OgImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OgImageController extends Controller
{
    public function __construct(
        protected OgImageService $ogImages,
    ) {}

    public function show(Request $request): Response
    {
        $articleId = $request->integer('id', 0);
        $jpeg = $this->ogImages->render($articleId > 0 ? $articleId : null);

        if ($jpeg === null) {
            abort(404);
        }

        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
