<?php

namespace App\Services\Newsletter;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class NewsletterSubscriptionService
{
    private const TABLE = 'newsletter_subscribers';

    public function subscribe(
        string $email,
        bool $consent,
        ?string $source = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        if (! Schema::hasTable(self::TABLE)) {
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

        $source = mb_substr(trim((string) $source) ?: 'newsletter', 0, 50);

        $existing = DB::table(self::TABLE)->where('email', $email)->first();

        if ($existing !== null) {
            if (($existing->status ?? '') === 'active') {
                return [
                    'ok' => true,
                    'message' => 'Vous êtes déjà abonné.',
                ];
            }

            DB::table(self::TABLE)
                ->where('id', $existing->id)
                ->update($this->recordAttributes($email, $source, $ipAddress, $userAgent, forUpdate: true));

            return [
                'ok' => true,
                'message' => 'Abonnement réactivé. Merci !',
            ];
        }

        DB::table(self::TABLE)->insert(
            $this->recordAttributes($email, $source, $ipAddress, $userAgent, forUpdate: false),
        );

        return [
            'ok' => true,
            'message' => 'Merci ! Vous êtes abonné.',
        ];
    }

    /** @return array<string, mixed> */
    private function recordAttributes(
        string $email,
        string $source,
        ?string $ipAddress,
        ?string $userAgent,
        bool $forUpdate,
    ): array {
        $attributes = [
            'status' => 'active',
            'consent' => Schema::hasColumn(self::TABLE, 'consent') ? 1 : null,
            'source' => $source,
        ];

        if (! $forUpdate) {
            $attributes['email'] = $email;
        }

        if ($attributes['consent'] === null) {
            unset($attributes['consent']);
        }

        if (Schema::hasColumn(self::TABLE, 'ip_address')) {
            $attributes['ip_address'] = $ipAddress;
        } elseif (Schema::hasColumn(self::TABLE, 'ip')) {
            $attributes['ip'] = $ipAddress;
        }

        if (Schema::hasColumn(self::TABLE, 'user_agent')) {
            $attributes['user_agent'] = $userAgent;
        }

        $now = now();

        if (Schema::hasColumn(self::TABLE, 'updated_at')) {
            $attributes['updated_at'] = $now;
        }

        if (! $forUpdate && Schema::hasColumn(self::TABLE, 'created_at')) {
            $attributes['created_at'] = $now;
        }

        return array_filter(
            $attributes,
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
