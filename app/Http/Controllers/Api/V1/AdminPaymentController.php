<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Admin\PaymentManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected PaymentManagementService $payments,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->payments->paginate(
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 10),
            search: $request->query('search'),
            status: $request->query('status'),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $paginator->items(),
                'stats' => $this->payments->stats(),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }
}
