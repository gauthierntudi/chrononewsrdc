<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\ArticleController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\JustForYouController;
use App\Http\Controllers\Web\LegacyFrontController;
use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\MediaRedirectController;
use App\Http\Controllers\Web\PrivacyController;
use App\Http\Controllers\Web\PublicationAjaxController;
use App\Http\Controllers\Web\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Front public — legacy via Laravel (styles Elementor + HTML d’origine)
| Blade natif (HomeController, CategoryController…) conservé pour Phase 4b.
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/accueil', '/');
Route::redirect('/home', '/');

Route::get('/categorie/{category}/{page?}', [CategoryController::class, 'show'])
    ->whereNumber('page')
    ->name('categories.show');

Route::get('/recherche', [SearchController::class, 'index'])->name('search');

Route::get('/juste-pour-vous/{page?}', [JustForYouController::class, 'index'])
    ->whereNumber('page')
    ->name('just-for-you');
Route::redirect('/url-page-just-for-you', '/juste-pour-vous');

/*
| Front public (article — migration progressive)
*/
Route::get('/article/{article}/{slug?}', [ArticleController::class, 'show'])
    ->whereNumber('article')
    ->name('articles.show');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::redirect('/nous-contacter', '/contact');

Route::get('/qui-sommes-nous', [AboutController::class, 'index'])->name('about');
Route::get('/politique-de-confidentialite', [PrivacyController::class, 'index'])->name('privacy');

Route::get('/og-image', [\App\Http\Controllers\Web\OgImageController::class, 'show'])->name('og-image');
Route::redirect('/og-image.php', '/og-image');

/*
| Médias legacy (/uploads/…) — redirige vers S3 quand MEDIA_DISK=s3
*/
Route::get('/uploads/{path}', [MediaRedirectController::class, 'uploads'])
    ->where('path', '.*')
    ->name('media.uploads');

/*
| Legacy publication AJAX (compatibilité front — sans .php)
|--------------------------------------------------------------------------
*/
Route::get('/publication/ajax/get-ad', [PublicationAjaxController::class, 'getAd']);
Route::get('/publication/ajax/live-search', [PublicationAjaxController::class, 'liveSearch']);
Route::post('/publication/ajax/track-ad', [PublicationAjaxController::class, 'trackAd']);

/** Anciennes URLs (.php) — compat laravel.cloud */
Route::get('/publication/ajax/get_ad.php', [PublicationAjaxController::class, 'getAd']);
Route::post('/publication/ajax/track_ad', [PublicationAjaxController::class, 'trackAd']);
Route::post('/publication/ajax/track_ad.php', [PublicationAjaxController::class, 'trackAd']);
Route::get('/publication/ajax/live-search.php', [PublicationAjaxController::class, 'liveSearch']);

/*
|--------------------------------------------------------------------------
| Auth & backoffice
|--------------------------------------------------------------------------
*/
Route::get('/connexion', [LoginController::class, 'show'])->name('login');
Route::redirect('/login', '/connexion');

Route::prefix('auth')->group(function (): void {
    Route::get('check-session', [AuthController::class, 'checkSession']);
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('request-otp', [AuthController::class, 'requestOtp'])->middleware('throttle:5,1');
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:5,1');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/publish', [DashboardController::class, 'publish'])->name('dashboard.publish');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->name('dashboard.admin')
        ->middleware('role:admin,superadmin');
    Route::get('/dashboard/admin/publish', [DashboardController::class, 'publishAdmin'])
        ->name('dashboard.admin.publish')
        ->middleware('role:admin,superadmin');
});
