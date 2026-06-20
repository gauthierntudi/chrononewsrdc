<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class NewsletterSubscriptionService
{
    public function subscribe(
        string $email,
        bool $consent,
        ?string $source = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        if (! Schema::hasTable('newsletter_subscribers')) {
            throw ValidationException::withMessages([
                'email' => ['Service newsletter indisponible.'],
            ]);
        }

        $email = mb_strtolower(trim($email));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => ['Email invalide.'],
            ]);
        }

        if (! $consent) {
            throw ValidationException::withMessages([
                'consent' => ['Veuillez accepter les termes.'],
            ]);
        }

        $source = trim((string) $source) ?: 'newsletter';
        $source = mb_substr($source, 0, 50);

        $existing = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($existing !== null) {
            if ($existing->status === 'active') {
                return [
                    'ok' => true,
                    'message' => 'Vous êtes déjà abonné.',
                ];
            }

            $existing->update([
                'status' => 'active',
                'consent' => true,
                'source' => $source,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return [
                'ok' => true,
                'message' => 'Abonnement réactivé. Merci !',
            ];
        }

        NewsletterSubscriber::query()->create([
            'email' => $email,
            'status' => 'active',
            'consent' => true,
            'source' => $source,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        return [
            'ok' => true,
            'message' => 'Merci ! Vous êtes abonné.',
        ];
    }
}
