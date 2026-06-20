<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Admin\NewsletterManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNewsletterController extends Controller
{
    public function __construct(
        protected NewsletterManagementService $newsletter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->newsletter->paginate(
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 10),
            search: $request->query('search'),
            status: $request->query('status'),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'subscribers' => $paginator->items(),
                'stats' => $this->newsletter->stats(),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function toggleStatus(int $subscriber): JsonResponse
    {
        return response()->json($this->newsletter->toggleStatus($subscriber));
    }

    public function destroy(int $subscriber): JsonResponse
    {
        return response()->json($this->newsletter->delete($subscriber));
    }
}
