<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->role?->usesAdminPanel()) {
            return redirect()->route('dashboard.admin');
        }

        return view('dashboard.user', [
            'user' => $user,
            'access' => \App\Support\DashboardAccess::for($user),
        ]);
    }

    public function admin(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->role?->usesAdminPanel()) {
            return redirect()->route('dashboard');
        }

        return view('dashboard.admin', [
            'user' => $user,
            'access' => \App\Support\DashboardAccess::for($user),
        ]);
    }

    public function publish(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->role?->usesAdminPanel()) {
            return redirect()->route('dashboard.admin.publish', request()->only('id'));
        }

        return view('dashboard.publish', [
            'user' => $user,
            'access' => \App\Support\DashboardAccess::for($user),
            'articleId' => request()->query('id'),
            'isAdmin' => false,
            'backUrl' => route('dashboard'),
        ]);
    }

    public function publishAdmin(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->role?->usesAdminPanel()) {
            return redirect()->route('dashboard.publish', request()->only('id'));
        }

        return view('dashboard.publish', [
            'user' => $user,
            'access' => \App\Support\DashboardAccess::for($user),
            'articleId' => request()->query('id'),
            'isAdmin' => true,
            'backUrl' => route('dashboard.admin'),
        ]);
    }
}
