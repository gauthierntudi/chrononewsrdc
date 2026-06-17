<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiTextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AiController extends Controller
{
    public function __construct(
        protected AiTextService $ai,
    ) {}

    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            ...$this->ai->publicConfig(),
        ]);
    }

    public function process(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:correct,rewrite,improve'],
            'text' => ['required', 'string', 'max:20000'],
        ]);

        try {
            $result = $this->ai->process($data['action'], $data['text']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'text' => $result,
        ]);
    }
}
