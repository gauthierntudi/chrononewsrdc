<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdvertisementRateManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAdvertisementRateController extends Controller
{
    public function __construct(
        protected AdvertisementRateManagementService $rates,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'rates' => $this->rates->list(),
        ]);
    }

    public function update(Request $request, int $rate): JsonResponse
    {
        $data = $request->validate([
            'price_7_days' => ['required', 'numeric', 'min:0'],
            'price_15_days' => ['required', 'numeric', 'min:0'],
            'price_30_days' => ['required', 'numeric', 'min:0'],
        ]);

        $this->rates->update(
            $rate,
            (float) $data['price_7_days'],
            (float) $data['price_15_days'],
            (float) $data['price_30_days'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Tarif mis à jour avec succès',
        ]);
    }
}
