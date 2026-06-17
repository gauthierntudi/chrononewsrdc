<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdvertisementManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAdvertisementController extends Controller
{
    public function __construct(
        protected AdvertisementManagementService $advertisements,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->advertisements->paginate(
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 10),
            search: $request->query('search'),
            validation: $request->query('validation'),
            payment: $request->query('payment'),
            broadcast: $request->query('broadcast'),
            placement: $request->query('placement'),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'advertisements' => $paginator->items(),
                'stats' => $this->advertisements->stats(),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function validateAd(int $advertisement): JsonResponse
    {
        return response()->json($this->advertisements->validate($advertisement));
    }

    public function refuse(Request $request, int $advertisement): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json($this->advertisements->refuse($advertisement, $data['reason']));
    }

    public function activate(int $advertisement): JsonResponse
    {
        return response()->json($this->advertisements->setBroadcast($advertisement, 'active'));
    }

    public function deactivate(int $advertisement): JsonResponse
    {
        return response()->json($this->advertisements->setBroadcast($advertisement, 'inactive'));
    }

    public function show(int $advertisement): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'advertisement' => $this->advertisements->show($advertisement),
            ],
        ]);
    }

    public function update(Request $request, int $advertisement): JsonResponse
    {
        return response()->json($this->advertisements->update($advertisement, $request->all()));
    }

    public function destroy(int $advertisement): JsonResponse
    {
        return response()->json($this->advertisements->delete($advertisement));
    }

    public function updateSchedule(Request $request, int $advertisement): JsonResponse
    {
        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
        ]);

        return response()->json($this->advertisements->updateSchedule(
            $advertisement,
            $data['starts_at'],
            $data['ends_at'],
        ));
    }
}
