<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Media\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class UploadController extends Controller
{
    public function __construct(
        protected UploadService $uploads,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'type' => ['required', 'in:image,video,profile,ad'],
            'ad_format' => ['nullable', 'string'],
        ]);

        try {
            $result = $this->uploads->handle(
                $request->file('file'),
                $request->string('type')->toString(),
                $request->input('ad_format'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fichier uploadé avec succès',
            'url' => $result['url'],
            'filename' => $result['filename'],
        ]);
    }
}
