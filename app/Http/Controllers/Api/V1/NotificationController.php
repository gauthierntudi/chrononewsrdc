<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ValidationStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Article\LegacyArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, LegacyArticleService $legacy): JsonResponse
    {
        $user = $request->user();
        $limit = 6;

        if ($user->isAdmin()) {
            $articles = $legacy->shouldUseLegacy()
                ? $legacy->pendingForAdmin($limit)
                : Article::query()
                    ->with('author:id,nom,mail')
                    ->where('validation_status', ValidationStatus::Pending)
                    ->latest()
                    ->limit($limit)
                    ->get();
        } else {
            $articles = $legacy->shouldUseLegacy()
                ? $legacy->notificationsForUser((int) $user->id, $limit)
                : Article::query()
                    ->where('user_id', $user->id)
                    ->whereIn('validation_status', [ValidationStatus::Pending, ValidationStatus::Rejected])
                    ->latest()
                    ->limit($limit)
                    ->get();
        }

        return response()->json([
            'success' => true,
            'articles' => $articles,
        ]);
    }
}
