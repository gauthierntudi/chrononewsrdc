<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Services\Admin\SocialMediaSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function __construct(
        protected SocialMediaSettingsService $socialMedia,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'settings' => Setting::allAsMap(),
            'social_media' => $this->socialMedia->get(),
            'social_media_catalog' => $this->socialMedia->catalog(),
            'subscription_plans' => SubscriptionPlan::query()->orderBy('price')->get(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if ($request->has('default_article_price')) {
            $data = $request->validate([
                'default_article_price' => ['required', 'numeric', 'min:0'],
            ]);

            Setting::setValue(
                'default_article_price',
                number_format((float) $data['default_article_price'], 2, '.', ''),
            );

            return response()->json([
                'success' => true,
                'message' => 'Prix par défaut enregistré',
            ]);
        }

        if ($request->has('social_media')) {
            $data = $request->validate([
                'social_media' => ['required', 'array'],
            ]);

            $this->socialMedia->update($data['social_media']);

            return response()->json([
                'success' => true,
                'message' => 'Réseaux sociaux enregistrés',
                'social_media' => $this->socialMedia->get(),
            ]);
        }

        $data = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable', 'string'],
        ]);

        Setting::setValue($data['key'], $data['value'] ?? '');

        return response()->json([
            'success' => true,
            'message' => 'Paramètre mis à jour',
        ]);
    }
}
