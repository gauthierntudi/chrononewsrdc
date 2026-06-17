<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(
        protected UserManagementService $users,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->users->paginate(
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 10),
            search: $request->query('search'),
        );

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $paginator->items(),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $result = $this->users->create($request->all());

        return response()->json($result, 201);
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        return response()->json($this->users->toggleStatus($data['user_id']));
    }

    public function updateRole(Request $request, int $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'in:user,journaliste,admin,superadmin'],
        ]);

        return response()->json($this->users->updateRole($user, $data['role']));
    }

    public function show(int $user): JsonResponse
    {
        $model = $this->users->find($user);

        return response()->json([
            'success' => true,
            'user' => $model->toAdminArray(),
        ]);
    }

    public function update(Request $request, int $user): JsonResponse
    {
        $result = $this->users->update($user, $request->all(), $request->user());

        return response()->json($result);
    }
}
