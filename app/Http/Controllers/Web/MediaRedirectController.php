<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Media\MediaUrlService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class MediaRedirectController extends Controller
{
    public function __construct(
        private readonly MediaUrlService $mediaUrls,
    ) {}

    public function uploads(string $path): RedirectResponse|Response
    {
        $relative = 'uploads/'.ltrim($path, '/');
        $url = $this->mediaUrls->url($relative);

        if ($url === '' || (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://'))) {
            abort(404);
        }

        return redirect()->away($url, 302);
    }
}
