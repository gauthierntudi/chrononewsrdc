<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FlexPayService
{
    protected function merchant(): string
    {
        return (string) config('chrononews.flexpay.merchant', '');
    }

    protected function token(): string
    {
        $token = trim((string) config('chrononews.flexpay.token', ''));
        if ($token !== '' && ! str_starts_with($token, 'Bearer ')) {
            return 'Bearer '.$token;
        }

        return $token;
    }

    /** @return array{type: string, url: string, data: array<string, mixed>} */
    public function preparePayment(
        string $transactionId,
        float $amount,
        string $method,
        array $userInfo,
        string $description = 'Paiement Chrononews',
    ): array {
        $phone = preg_replace('/[^0-9]/', '', (string) ($userInfo['telephone'] ?? ''));
        if (preg_match('/^0(\d{9})$/', $phone, $matches)) {
            $phone = '243'.$matches[1];
        }

        if ($method === 'carte_bancaire') {
            $siteUrl = rtrim((string) config('chrononews.url', config('app.url')), '/');
            $returnBaseUrl = $siteUrl.'/dashboard?view=ads&payment_ref='.$transactionId;

            return [
                'type' => 'card',
                'url' => (string) config('chrononews.flexpay.card_url'),
                'data' => [
                    'authorization' => $this->token(),
                    'merchant' => $this->merchant(),
                    'reference' => $transactionId,
                    'amount' => $amount,
                    'currency' => config('chrononews.article.currency', 'USD'),
                    'description' => $description,
                    'approve_url' => $returnBaseUrl.'&payment_status=success',
                    'cancel_url' => $returnBaseUrl.'&payment_status=cancel',
                    'decline_url' => $returnBaseUrl.'&payment_status=decline',
                    'home_url' => $siteUrl.'/dashboard?view=ads',
                    'callback_url' => $siteUrl.'/api/v1/webhooks/flexpay',
                ],
            ];
        }

        return [
            'type' => 'mobile',
            'url' => (string) config('chrononews.flexpay.mobile_url'),
            'data' => [
                'merchant' => $this->merchant(),
                'type' => '1',
                'reference' => $transactionId,
                'phone' => $phone,
                'amount' => $amount,
                'currency' => config('chrononews.article.currency', 'USD'),
                'callbackUrl' => rtrim((string) config('chrononews.url', config('app.url')), '/').'/api/v1/webhooks/flexpay',
            ],
        ];
    }

    /** @return array{success: bool, orderNumber?: string, message?: string} */
    public function callMobileApi(array $paymentData): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $this->token(),
            ])
                ->timeout(120)
                ->withOptions(['verify' => false])
                ->post($paymentData['url'], $paymentData['data']);

            Log::info('FlexPay mobile response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erreur HTTP FlexPay: '.$response->status(),
                ];
            }

            $result = $response->json();
            if (isset($result['code']) && (string) $result['code'] === '0') {
                return [
                    'success' => true,
                    'orderNumber' => $result['orderNumber'] ?? null,
                    'message' => $result['message'] ?? 'Paiement initié',
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Erreur FlexPay inconnue',
            ];
        } catch (\Throwable $e) {
            Log::error('FlexPay mobile error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Erreur connexion FlexPay: '.$e->getMessage(),
            ];
        }
    }

    public function checkTransactionStatus(string $orderNumber): ?string
    {
        $url = rtrim((string) config('chrononews.flexpay.check_url'), '/').'/'.$orderNumber;

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token(),
            ])
                ->timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $result = $response->json();
            if (isset($result['code'], $result['transaction']['status']) && (string) $result['code'] === '0') {
                return (string) $result['transaction']['status'];
            }
        } catch (\Throwable $e) {
            Log::error('FlexPay check error', ['orderNumber' => $orderNumber, 'error' => $e->getMessage()]);
        }

        return null;
    }

    public function usesLegacySchema(): bool
    {
        return Schema::hasTable('paiements');
    }

    protected function paymentsTable(): string
    {
        return $this->usesLegacySchema() ? 'paiements' : 'payments';
    }

    public function updatePaymentStatus(int $paymentId, string $status): void
    {
        DB::table($this->paymentsTable())
            ->where('id', $paymentId)
            ->update([
                $this->usesLegacySchema() ? 'statut' : 'status' => $status,
                'updated_at' => now(),
            ]);
    }

    /** @return array{success: bool, message: string} */
    public function handleCallback(string $orderNumber, string $statusCode): array
    {
        $table = $this->paymentsTable();
        $orderColumn = $this->usesLegacySchema() ? 'maxicash_transaction_id' : 'provider_order_number';
        $statusColumn = $this->usesLegacySchema() ? 'statut' : 'status';

        $payment = DB::table($table)->where($orderColumn, $orderNumber)->first();
        if (! $payment) {
            return ['success' => false, 'message' => 'Paiement non trouvé'];
        }

        $currentStatus = $payment->{$statusColumn} ?? '';
        if (in_array($currentStatus, ['reussi', 'paid', 'succeeded'], true)) {
            return ['success' => true, 'message' => 'Déjà traité'];
        }

        $newStatus = $statusCode === '0'
            ? ($this->usesLegacySchema() ? 'reussi' : 'paid')
            : ($this->usesLegacySchema() ? 'echoue' : 'failed');

        $this->updatePaymentStatus((int) $payment->id, $newStatus);

        if ($newStatus === 'reussi' || $newStatus === 'paid') {
            $this->activatePaidResource($payment);
        }

        return ['success' => true, 'message' => 'Statut mis à jour'];
    }

    protected function activatePaidResource(object $payment): void
    {
        if ($this->usesLegacySchema()) {
            if (! empty($payment->publicite_id) && Schema::hasTable('publicites')) {
                DB::table('publicites')
                    ->where('id', $payment->publicite_id)
                    ->update(['statut_paiement' => 'paye', 'updated_at' => now()]);
            }

            if (! empty($payment->actualite_id) && Schema::hasTable('actualites')) {
                DB::table('actualites')
                    ->where('id', $payment->actualite_id)
                    ->update(['statut_paiement' => 'paye', 'updated_at' => now()]);
            }

            return;
        }

        if (! empty($payment->advertisement_id) && Schema::hasTable('advertisements')) {
            DB::table('advertisements')
                ->where('id', $payment->advertisement_id)
                ->update(['payment_status' => 'paid', 'updated_at' => now()]);
        }
    }

    /** @return array{success: bool, statut?: string, message?: string} */
    public function checkLocalStatus(string $orderNumber): array
    {
        $table = $this->paymentsTable();
        $orderColumn = $this->usesLegacySchema() ? 'maxicash_transaction_id' : 'provider_order_number';
        $statusColumn = $this->usesLegacySchema() ? 'statut' : 'status';

        $payment = DB::table($table)->where($orderColumn, $orderNumber)->first();
        if (! $payment) {
            return ['success' => false, 'message' => 'Paiement non trouvé'];
        }

        $status = (string) ($payment->{$statusColumn} ?? 'en_attente');
        $pendingStatuses = ['en_attente', 'pending'];

        if (in_array($status, $pendingStatuses, true)) {
            $apiStatus = $this->checkTransactionStatus($orderNumber);
            if ($apiStatus === '0' || $apiStatus === '1') {
                $this->handleCallback($orderNumber, $apiStatus);
                $status = $apiStatus === '0'
                    ? ($this->usesLegacySchema() ? 'reussi' : 'paid')
                    : ($this->usesLegacySchema() ? 'echoue' : 'failed');
            }
        }

        return ['success' => true, 'statut' => $status];
    }
}
