<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Admin\PaymentManagementService;
use App\Services\Payment\FlexPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManagementService $payments,
        protected FlexPayService $flexPay,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->role?->canViewOwnPayments()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé',
            ], 403);
        }

        $paginator = $this->payments->paginate(
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 10),
            search: $request->query('search'),
            status: $request->query('status'),
            userId: (int) $user->id,
        );

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $paginator->items(),
                'stats' => $this->payments->stats((int) $user->id),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function checkStatus(Request $request): JsonResponse
    {
        $orderNumber = $request->query('orderNumber');
        if (! $orderNumber) {
            return response()->json([
                'success' => false,
                'message' => 'OrderNumber requis',
            ], 422);
        }

        $result = $this->flexPay->checkLocalStatus((string) $orderNumber);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 404);
    }

    public function subscriptionStatus(Request $request): JsonResponse
    {
        if (! Schema::hasTable('user_subscriptions')) {
            return response()->json(['success' => true, 'subscription' => null]);
        }

        $userId = (int) $request->user()->id;
        $planFk = Schema::hasColumn('user_subscriptions', 'plan_id') ? 'plan_id' : 'subscription_plan_id';
        $endCol = Schema::hasColumn('user_subscriptions', 'end_date') ? 'end_date' : 'ends_at';

        $subscription = DB::table('user_subscriptions as us')
            ->join('subscription_plans as sp', 'us.'.$planFk, '=', 'sp.id')
            ->where('us.user_id', $userId)
            ->where('us.status', 'active')
            ->where('us.'.$endCol, '>', now())
            ->orderByDesc('us.'.$endCol)
            ->select('us.*', 'sp.name as plan_name')
            ->first();

        return response()->json([
            'success' => true,
            'subscription' => $subscription,
        ]);
    }
}
