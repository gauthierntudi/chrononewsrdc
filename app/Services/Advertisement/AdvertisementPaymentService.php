<?php

namespace App\Services\Advertisement;

use App\Models\User;
use App\Services\Payment\FlexPayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AdvertisementPaymentService
{
    public function __construct(
        protected UserAdvertisementService $advertisements,
        protected FlexPayService $flexPay,
    ) {}

    /** @return array<string, mixed> */
    public function initiate(User $user, int $advertisementId, float $amount, string $method, string $phone): array
    {
        $ad = $this->advertisements->showForUser($user, $advertisementId);

        if ($user->role?->adsAreFree()) {
            throw ValidationException::withMessages([
                'payment' => ['Les publicités sont gratuites pour votre rôle.'],
            ]);
        }

        $paymentStatus = $ad['payment_status'] ?? 'en_attente';
        if (in_array($paymentStatus, ['paye', 'gratuit', 'paid', 'free'], true)) {
            throw ValidationException::withMessages([
                'payment' => ['Cette publicité est déjà payée.'],
            ]);
        }

        $expectedAmount = (float) ($ad['amount_paid'] ?? 0);
        if ($expectedAmount > 0 && abs($expectedAmount - $amount) > 0.01) {
            $amount = $expectedAmount;
        }

        if ($this->flexPay->usesLegacySchema()) {
            return $this->initiateLegacy($user, $advertisementId, $amount, $method, $phone);
        }

        return $this->initiateModern($user, $advertisementId, $amount, $method, $phone);
    }

    /** @return array<string, mixed> */
    protected function initiateLegacy(User $user, int $advertisementId, float $amount, string $method, string $phone): array
    {
        $pub = DB::table('publicites')
            ->leftJoin('users', 'publicites.user_id', '=', 'users.id')
            ->where('publicites.id', $advertisementId)
            ->select('publicites.*', 'users.nom', 'users.mail as email', 'users.telephone as user_phone')
            ->first();

        if (! $pub) {
            throw ValidationException::withMessages(['id' => ['Publicité introuvable.']]);
        }

        $existing = DB::table('paiements')
            ->where('publicite_id', $advertisementId)
            ->first();

        $transactionId = 'PUB'.time().rand(1000, 9999);

        if ($existing) {
            $paymentId = (int) $existing->id;
            DB::table('paiements')->where('id', $paymentId)->update([
                'methode' => $method,
                'transaction_id' => $transactionId,
                'maxicash_transaction_id' => null,
                'statut' => 'en_attente',
                'montant' => $amount,
                'updated_at' => now(),
            ]);
        } else {
            $paymentId = (int) DB::table('paiements')->insertGetId([
                'user_id' => $pub->user_id,
                'publicite_id' => $advertisementId,
                'montant' => $amount,
                'methode' => $method,
                'transaction_id' => $transactionId,
                'statut' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $userInfo = [
            'nom' => $pub->nom ?? $user->name ?? 'Annonceur',
            'email' => $pub->email ?? $user->email ?? '',
            'telephone' => $phone ?: ($pub->user_phone ?? ''),
        ];

        $flexData = $this->flexPay->preparePayment(
            $transactionId,
            $amount,
            $method,
            $userInfo,
            'Paiement publicité #'.$advertisementId,
        );

        if ($flexData['type'] === 'card') {
            return [
                'success' => true,
                'is_redirect' => true,
                'payment_url' => $flexData['url'],
                'params' => $flexData['data'],
            ];
        }

        $response = $this->flexPay->callMobileApi($flexData);
        if (! $response['success']) {
            $this->flexPay->updatePaymentStatus($paymentId, 'echoue');

            return [
                'success' => false,
                'message' => 'Erreur FlexPay: '.($response['message'] ?? 'Erreur inconnue'),
            ];
        }

        DB::table('paiements')->where('id', $paymentId)->update([
            'maxicash_transaction_id' => $response['orderNumber'],
            'updated_at' => now(),
        ]);

        return [
            'success' => true,
            'direct_success' => true,
            'orderNumber' => $response['orderNumber'],
            'message' => $response['message'] ?? 'Paiement initié. Validez sur votre téléphone.',
        ];
    }

    /** @return array<string, mixed> */
    protected function initiateModern(User $user, int $advertisementId, float $amount, string $method, string $phone): array
    {
        if (! Schema::hasTable('payments')) {
            throw ValidationException::withMessages([
                'payment' => ['Table paiements introuvable.'],
            ]);
        }

        $existing = DB::table('payments')
            ->where('advertisement_id', $advertisementId)
            ->first();

        $transactionId = 'PUB'.time().rand(1000, 9999);

        if ($existing) {
            $paymentId = (int) $existing->id;
            DB::table('payments')->where('id', $paymentId)->update([
                'method' => $method,
                'transaction_id' => $transactionId,
                'provider_order_number' => null,
                'status' => 'pending',
                'amount' => $amount,
                'updated_at' => now(),
            ]);
        } else {
            $paymentId = (int) DB::table('payments')->insertGetId([
                'user_id' => $user->id,
                'advertisement_id' => $advertisementId,
                'amount' => $amount,
                'method' => $method,
                'transaction_id' => $transactionId,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $userInfo = [
            'nom' => $user->name ?? 'Annonceur',
            'email' => $user->email ?? '',
            'telephone' => $phone,
        ];

        $flexData = $this->flexPay->preparePayment(
            $transactionId,
            $amount,
            $method,
            $userInfo,
            'Paiement publicité #'.$advertisementId,
        );

        if ($flexData['type'] === 'card') {
            return [
                'success' => true,
                'is_redirect' => true,
                'payment_url' => $flexData['url'],
                'params' => $flexData['data'],
            ];
        }

        $response = $this->flexPay->callMobileApi($flexData);
        if (! $response['success']) {
            $this->flexPay->updatePaymentStatus($paymentId, 'failed');

            return [
                'success' => false,
                'message' => 'Erreur FlexPay: '.($response['message'] ?? 'Erreur inconnue'),
            ];
        }

        DB::table('payments')->where('id', $paymentId)->update([
            'provider_order_number' => $response['orderNumber'],
            'updated_at' => now(),
        ]);

        return [
            'success' => true,
            'direct_success' => true,
            'orderNumber' => $response['orderNumber'],
            'message' => $response['message'] ?? 'Paiement initié. Validez sur votre téléphone.',
        ];
    }
}
