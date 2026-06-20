<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Advertisement\FrontAdvertisementService;
use App\Services\Media\MediaUrlService;
use App\Services\Newsletter\NewsletterSubscriptionService;
use App\Support\FrontHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicationAjaxController extends Controller
{
    public function __construct(
        private readonly FrontAdvertisementService $ads,
        private readonly MediaUrlService $mediaUrls,
        private readonly NewsletterSubscriptionService $newsletter,
    ) {}

    public function getAd(Request $request): JsonResponse
    {
        $format = trim((string) $request->query('format', 'paysage_small'));

        if (! in_array($format, FrontAdvertisementService::FORMATS, true)) {
            return response()->json(['ok' => false, 'error' => 'invalid_format'], 400);
        }

        if ($format === 'video-outstream') {
            $video = $this->ads->pickVideoOutstream();

            return response()->json([
                'ok' => true,
                'ad' => $video,
            ]);
        }

        $emplacement = trim((string) $request->query('emplacement', ''));
        $emplacement = $emplacement !== '' ? $emplacement : null;

        $excludeIds = [];
        $excludeRaw = trim((string) $request->query('exclude', ''));
        if ($excludeRaw !== '') {
            foreach (explode(',', $excludeRaw) as $part) {
                $id = (int) trim($part);
                if ($id > 0) {
                    $excludeIds[] = $id;
                }
            }
        }

        $ad = $this->ads->pick($format, $emplacement, $excludeIds);

        if ($ad === null) {
            return response()->json(['ok' => true, 'ad' => null]);
        }

        return response()->json([
            'ok' => true,
            'ad' => [
                'id' => (int) $ad['id'],
                'title' => $ad['titre'] ?? 'Sponsored',
                'img' => $this->mediaUrls->url((string) ($ad['image_url'] ?? '')),
                'url' => $ad['url_cible'] ?? '#',
                'format' => $ad['format'] ?? $format,
            ],
        ]);
    }

    public function trackAd(Request $request): JsonResponse
    {
        if (! $request->isMethod('post')) {
            return response()->json(['ok' => false, 'error' => 'method_not_allowed'], 405);
        }

        $adId = (int) $request->input('ad_id', 0);
        $event = trim((string) $request->input('event', ''));

        if ($adId <= 0 || ! in_array($event, ['view', 'click'], true)) {
            return response()->json(['ok' => false, 'error' => 'bad_request'], 400);
        }

        $this->ads->track($adId, $event);

        return response()->json(['ok' => true]);
    }

    public function liveSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = max(1, min(10, (int) $request->query('limit', 6)));

        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        $rows = DB::table('actualites')
            ->select(['id', 'titre', 'cover', 'date_add', 'created_at'])
            ->where('status', 1)
            ->where('statut_validation', 'valide')
            ->where(function ($query) use ($q): void {
                $query->where('titre', 'like', '%'.$q.'%')
                    ->orWhere('contenu', 'like', '%'.$q.'%');
            })
            ->orderByDesc('date_add')
            ->limit($limit)
            ->get();

        $items = $rows->map(function (object $row) {
            $title = FrontHelper::cleanTitle($row->titre ?? '');
            $covers = FrontHelper::parseCoverImages($row->cover ?? null);
            $imagePath = $covers[0] ?? null;

            return [
                'id' => (int) $row->id,
                'title' => $title,
                'url' => FrontHelper::articleUrl(['id' => $row->id, 'titre' => $title]),
                'date' => FrontHelper::formatDate(($row->date_add ?? '') ?: ($row->created_at ?? '')),
                'image' => $imagePath ? $this->mediaUrls->url($imagePath) : null,
            ];
        })->values()->all();

        return response()->json(['items' => $items]);
    }

    public function newsletterSubscribe(Request $request): JsonResponse
    {
        if (! $request->isMethod('post')) {
            return response()->json(['ok' => false, 'message' => 'Method not allowed'], 405);
        }

        try {
            $result = $this->newsletter->subscribe(
                email: (string) $request->input('your-email', ''),
                consent: $request->input('your-consent') === '1'
                    || $request->boolean('your-consent'),
                source: $request->input('source'),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return response()->json($result);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?: 'Données invalides.';

            return response()->json(['ok' => false, 'message' => $message], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['ok' => false, 'message' => 'Erreur interne.'], 500);
        }
    }
}
