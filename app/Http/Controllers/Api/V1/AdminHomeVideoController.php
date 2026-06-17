<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomeVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminHomeVideoController extends Controller
{
    public function index(): JsonResponse
    {
        $videos = HomeVideo::query()->latest('id')->get();

        return response()->json([
            'success' => true,
            'videos' => $videos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'youtube_id' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $video = HomeVideo::query()->create([
            ...$data,
            'is_active' => $data['is_active'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vidéo ajoutée avec succès',
            'video' => $video,
        ], 201);
    }

    public function update(Request $request, HomeVideo $video): JsonResponse
    {
        $data = $request->validate([
            'youtube_id' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $video->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Vidéo mise à jour avec succès',
            'video' => $video->fresh(),
        ]);
    }

    public function destroy(HomeVideo $video): JsonResponse
    {
        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vidéo supprimée avec succès',
        ]);
    }

    public function toggleStatus(HomeVideo $video): JsonResponse
    {
        $video->update(['is_active' => ! $video->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'new_state' => $video->is_active,
        ]);
    }
}
