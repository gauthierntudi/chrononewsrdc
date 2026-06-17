<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomeVideo;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

class PublicSiteController extends Controller
{
    public function homeVideos(): JsonResponse
    {
        $videos = HomeVideo::query()
            ->where('is_active', true)
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'videos' => $videos,
        ]);
    }

    public function allHomeVideos(): JsonResponse
    {
        $videos = HomeVideo::query()->latest('id')->get();

        return response()->json([
            'success' => true,
            'videos' => $videos,
        ]);
    }

    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'settings' => Setting::allAsMap(),
        ]);
    }

    public function subscriptionPlans(): JsonResponse
    {
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }
}
