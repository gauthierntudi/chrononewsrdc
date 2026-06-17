<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (auth()->check()) {
            $redirect = $this->sanitizeRedirect(request()->query('redirect'));

            if ($redirect !== null) {
                return redirect()->to($this->appendBuyAction($redirect, request()->query('action')));
            }

            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'redirectUrl' => $this->sanitizeRedirect(request()->query('redirect')),
            'authAction' => request()->query('action') === 'buy' ? 'buy' : null,
        ]);
    }

    private function sanitizeRedirect(mixed $redirect): ?string
    {
        if (! is_string($redirect) || trim($redirect) === '') {
            return null;
        }

        $decoded = urldecode(trim($redirect));

        if (str_starts_with($decoded, '/')) {
            return $decoded;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $targetHost = parse_url($decoded, PHP_URL_HOST);

        if ($appHost && $targetHost && strcasecmp($appHost, $targetHost) === 0) {
            $path = parse_url($decoded, PHP_URL_PATH) ?: '/';
            $query = parse_url($decoded, PHP_URL_QUERY);
            $fragment = parse_url($decoded, PHP_URL_FRAGMENT);

            return $path
                .($query ? '?'.$query : '')
                .($fragment ? '#'.$fragment : '');
        }

        return null;
    }

    private function appendBuyAction(string $url, mixed $action): string
    {
        if ($action !== 'buy') {
            return $url;
        }

        $fragment = '';
        if (str_contains($url, '#')) {
            [$url, $fragment] = explode('#', $url, 2);
            $fragment = '#'.$fragment;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        if (str_contains($url, 'action=buy')) {
            return $url.$fragment;
        }

        return $url.$separator.'action=buy'.$fragment;
    }
}
