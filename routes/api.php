<?php

use App\Http\Controllers\Api\V1\AdminAdvertisementRateController;
use App\Http\Controllers\Api\V1\AdminAdvertisementController;
use App\Http\Controllers\Api\V1\AdvertisementController;
use App\Http\Controllers\Api\V1\AdminArticleController;
use App\Http\Controllers\Api\V1\AdminDashboardController;
use App\Http\Controllers\Api\V1\AdminHomeVideoController;
use App\Http\Controllers\Api\V1\AdminNewsletterController;
use App\Http\Controllers\Api\V1\AdminPaymentController;
use App\Http\Controllers\Api\V1\AdminSettingController;
use App\Http\Controllers\Api\V1\AdminSubscriptionPlanController;
use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\ArticleBlockController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardStatsController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PublicSiteController;
use App\Http\Controllers\Api\V1\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('site')->group(function (): void {
        Route::get('home-videos', [PublicSiteController::class, 'homeVideos']);
        Route::get('settings', [PublicSiteController::class, 'settings']);
        Route::get('subscription-plans', [PublicSiteController::class, 'subscriptionPlans']);
    });

    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('request-otp', [AuthController::class, 'requestOtp'])->middleware('throttle:5,1');
        Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:5,1');
        Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth');
        Route::get('me', [AuthController::class, 'me'])->middleware('auth');
        Route::put('profile', [AuthController::class, 'updateProfile'])->middleware('auth');
        Route::get('check-session', [AuthController::class, 'checkSession']);
    });

    Route::middleware('auth')->group(function (): void {
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('stats', [DashboardStatsController::class, 'index']);
        Route::get('payments', [PaymentController::class, 'index']);
        Route::get('payments/check-status', [PaymentController::class, 'checkStatus']);
        Route::get('subscription', [PaymentController::class, 'subscriptionStatus']);

        Route::post('upload', [UploadController::class, 'store']);

        Route::get('ai/status', [AiController::class, 'status']);
        Route::post('ai/process', [AiController::class, 'process'])->middleware('throttle:30,1');

        Route::get('articles', [ArticleController::class, 'index']);
        Route::post('articles', [ArticleController::class, 'store']);
        Route::get('articles/pending-count', [ArticleController::class, 'pendingCount']);
        Route::get('articles/{articleId}', [ArticleController::class, 'show'])->whereNumber('articleId');
        Route::put('articles/{articleId}', [ArticleController::class, 'update'])->whereNumber('articleId');
        Route::delete('articles/{articleId}', [ArticleController::class, 'destroy'])->whereNumber('articleId');
        Route::get('articles/{articleId}/tags', [ArticleController::class, 'tags'])->whereNumber('articleId');

        Route::get('articles/{articleId}/blocks', [ArticleBlockController::class, 'index'])->whereNumber('articleId');
        Route::post('articles/{articleId}/blocks', [ArticleBlockController::class, 'store'])->whereNumber('articleId');
        Route::put('blocks/{blockId}', [ArticleBlockController::class, 'update'])->whereNumber('blockId');
        Route::delete('blocks/{blockId}', [ArticleBlockController::class, 'destroy'])->whereNumber('blockId');

        Route::get('advertisement-rates', [AdminAdvertisementRateController::class, 'index']);

        Route::get('advertisements', [AdvertisementController::class, 'index']);
        Route::post('advertisements', [AdvertisementController::class, 'store']);
        Route::get('advertisements/{advertisement}', [AdvertisementController::class, 'show'])->whereNumber('advertisement');
        Route::put('advertisements/{advertisement}', [AdvertisementController::class, 'update'])->whereNumber('advertisement');
        Route::delete('advertisements/{advertisement}', [AdvertisementController::class, 'destroy'])->whereNumber('advertisement');
        Route::post('advertisements/{advertisement}/initiate-payment', [AdvertisementController::class, 'initiatePayment'])->whereNumber('advertisement');
    });

    Route::prefix('admin')->middleware(['auth', 'role:admin,superadmin'])->group(function (): void {
        Route::get('stats', [AdminDashboardController::class, 'stats']);
        Route::get('pending-count', [AdminDashboardController::class, 'pendingCount']);

        Route::get('articles', [AdminArticleController::class, 'index']);
        Route::get('articles/pending', [AdminArticleController::class, 'pending']);
        Route::get('articles/{articleId}', [AdminArticleController::class, 'show'])->whereNumber('articleId');
        Route::get('articles/{articleId}/blocks', [AdminArticleController::class, 'blocks'])->whereNumber('articleId');
        Route::post('articles/{articleId}/approve', [AdminArticleController::class, 'approve'])->whereNumber('articleId');
        Route::post('articles/{articleId}/reject', [AdminArticleController::class, 'reject'])->whereNumber('articleId');
        Route::delete('articles/bulk', [AdminArticleController::class, 'destroyMultiple']);
        Route::delete('articles/{articleId}', [AdminArticleController::class, 'destroyAdmin'])->whereNumber('articleId');

        Route::get('home-videos', [AdminHomeVideoController::class, 'index']);
        Route::post('home-videos', [AdminHomeVideoController::class, 'store']);
        Route::put('home-videos/{video}', [AdminHomeVideoController::class, 'update']);
        Route::delete('home-videos/{video}', [AdminHomeVideoController::class, 'destroy']);
        Route::post('home-videos/{video}/toggle', [AdminHomeVideoController::class, 'toggleStatus']);

        Route::get('settings', [AdminSettingController::class, 'index']);

        Route::get('advertisements', [AdminAdvertisementController::class, 'index']);
        Route::get('advertisements/{advertisement}', [AdminAdvertisementController::class, 'show'])->whereNumber('advertisement');
        Route::put('advertisements/{advertisement}', [AdminAdvertisementController::class, 'update'])->whereNumber('advertisement');
        Route::post('advertisements/{advertisement}/validate', [AdminAdvertisementController::class, 'validateAd'])->whereNumber('advertisement');
        Route::post('advertisements/{advertisement}/refuse', [AdminAdvertisementController::class, 'refuse'])->whereNumber('advertisement');
        Route::post('advertisements/{advertisement}/activate', [AdminAdvertisementController::class, 'activate'])->whereNumber('advertisement');
        Route::post('advertisements/{advertisement}/deactivate', [AdminAdvertisementController::class, 'deactivate'])->whereNumber('advertisement');
        Route::put('advertisements/{advertisement}/schedule', [AdminAdvertisementController::class, 'updateSchedule'])->whereNumber('advertisement');
        Route::delete('advertisements/{advertisement}', [AdminAdvertisementController::class, 'destroy'])->whereNumber('advertisement');
    });

    Route::prefix('admin')->middleware(['auth', 'role:superadmin'])->group(function (): void {
        Route::put('settings', [AdminSettingController::class, 'update']);

        Route::post('subscription-plans', [AdminSubscriptionPlanController::class, 'store']);
        Route::put('subscription-plans/{plan}', [AdminSubscriptionPlanController::class, 'update'])->whereNumber('plan');
        Route::delete('subscription-plans/{plan}', [AdminSubscriptionPlanController::class, 'destroy'])->whereNumber('plan');

        Route::get('payments', [AdminPaymentController::class, 'index']);

        Route::put('advertisement-rates/{rate}', [AdminAdvertisementRateController::class, 'update'])->whereNumber('rate');

        Route::get('users', [AdminUserController::class, 'index']);
        Route::get('users/{user}', [AdminUserController::class, 'show'])->whereNumber('user');
        Route::post('users', [AdminUserController::class, 'store']);
        Route::put('users/{user}', [AdminUserController::class, 'update'])->whereNumber('user');
        Route::post('users/toggle-status', [AdminUserController::class, 'toggleStatus']);
        Route::put('users/{user}/role', [AdminUserController::class, 'updateRole'])->whereNumber('user');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->whereNumber('user');

        Route::get('newsletter-subscribers', [AdminNewsletterController::class, 'index']);
        Route::post('newsletter-subscribers/{subscriber}/toggle-status', [AdminNewsletterController::class, 'toggleStatus'])->whereNumber('subscriber');
        Route::delete('newsletter-subscribers/{subscriber}', [AdminNewsletterController::class, 'destroy'])->whereNumber('subscriber');
    });

    Route::prefix('webhooks')->group(function (): void {
        // Phase 3 — FlexPay / MaxiCash
    });
});
