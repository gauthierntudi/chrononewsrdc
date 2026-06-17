<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Advertisement\AdvertisementPaymentService;
use App\Services\Advertisement\UserAdvertisementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function __construct(
        protected UserAdvertisementService $advertisements,
        protected AdvertisementPaymentService $payments,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->canManageOwnAds(), 403);

        $filters = array_filter([
            'search' => $request->query('search'),
            'validation' => $request->query('validation'),
            'payment' => $request->query('payment'),
            'broadcast' => $request->query('broadcast'),
            'placement' => $request->query('placement'),
        ], fn ($value) => $value !== null && $value !== '');

        $paginator = $this->advertisements->paginateForUser(
            $user,
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 10),
            filters: $filters,
        );

        return response()->json([
            'success' => true,
            'data' => [
                'advertisements' => $paginator->items(),
                'stats' => $this->advertisements->statsForUser($user),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $advertisement): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->canManageOwnAds(), 403);

        return response()->json([
            'success' => true,
            'data' => [
                'advertisement' => $this->advertisements->showForUser($user, $advertisement),
            ],
        ]);
    }

    public function initiatePayment(Request $request, int $advertisement): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->canManageOwnAds(), 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'in:carte_bancaire,mpesa,airtel_money,orange_money'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $result = $this->payments->initiate(
            $user,
            $advertisement,
            (float) $validated['amount'],
            $validated['method'],
            $validated['phone'],
        );

        $status = ($result['success'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->canManageOwnAds(), 403);

        $result = $this->advertisements->create($user, $request->all());

        return response()->json($result, 201);
    }

    public function update(Request $request, int $advertisement): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->canManageOwnAds(), 403);

        return response()->json($this->advertisements->update($user, $advertisement, $request->all()));
    }

    public function destroy(Request $request, int $advertisement): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role?->canManageOwnAds(), 403);

        return response()->json($this->advertisements->delete($user, $advertisement));
    }
}
