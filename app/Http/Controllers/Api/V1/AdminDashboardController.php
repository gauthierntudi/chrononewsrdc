<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminStatsService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AdminStatsService $stats,
    ) {}

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'stats' => $this->stats->compile(),
        ]);
    }

    public function pendingCount(): JsonResponse
    {
        $stats = $this->stats->compile();

        return response()->json([
            'success' => true,
            'count' => (int) ($stats['articles_pending'] ?? 0),
        ]);
    }
}
