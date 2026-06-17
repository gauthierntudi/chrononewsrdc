<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthorStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{
    public function index(Request $request, AuthorStatsService $stats): JsonResponse
    {
        return response()->json([
            'success' => true,
            'stats' => $stats->compile($request->user()),
        ]);
    }
}
