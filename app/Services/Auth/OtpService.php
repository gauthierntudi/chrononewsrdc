<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public function register(string $name, string $email): array
    {
        if (User::query()->where('mail', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Cet email est déjà utilisé.'],
            ]);
        }

        $this->ensureSendAllowed($email);

        $otp = $this->generateCode();

        $user = User::query()->create([
            'nom' => $name,
            'mail' => $email,
            'role' => 'user',
            'otp' => $otp,
            'otp_expiration' => now()->addMinutes($this->expiresMinutes()),
            'num_user' => 'USR'.time().random_int(1000, 9999),
            'cover' => '',
            'mdp' => '',
            'status' => 1,
        ]);

        $this->sendOtp($user, $otp);
        $this->recordSendAttempt($email);

        return [
            'success' => true,
            'message' => 'Inscription réussie. Vérifiez votre email pour le code OTP.',
            'cooldown_seconds' => $this->resendCooldownSeconds(),
        ];
    }

    public function requestLoginOtp(string $email): array
    {
        $user = User::query()->where('mail', $email)->where('status', 1)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Aucun compte avec cet email. Créez un compte pour continuer votre achat.'],
            ]);
        }

        return $this->deliverOtp($user);
    }

    public function resendLoginOtp(string $email): array
    {
        $user = User::query()->where('mail', $email)->where('status', 1)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Aucun compte avec cet email. Créez un compte pour continuer.'],
            ]);
        }

        return $this->deliverOtp($user, resend: true);
    }

    protected function deliverOtp(User $user, bool $resend = false): array
    {
        $email = trim((string) $user->mail);
        $this->ensureSendAllowed($email);

        $otp = $this->generateCode();

        $user->update([
            'otp' => $otp,
            'otp_expiration' => now()->addMinutes($this->expiresMinutes()),
        ]);

        $this->sendOtp($user, $otp);
        $this->recordSendAttempt($email);

        return [
            'success' => true,
            'message' => $resend
                ? 'Un nouveau code OTP a été envoyé à votre email.'
                : 'Code OTP envoyé à votre email.',
            'cooldown_seconds' => $this->resendCooldownSeconds(),
        ];
    }

    public function verify(string $email, string $otp): User
    {
        $this->ensureNotRateLimited('otp-verify', $email);

        $user = User::query()
            ->where('mail', $email)
            ->where('otp', $otp)
            ->where('otp_expiration', '>', now())
            ->where('status', 1)
            ->first();

        if (! $user) {
            RateLimiter::hit($this->rateLimitKey('otp-verify', $email), 900);

            throw ValidationException::withMessages([
                'otp' => ['Code OTP invalide ou expiré.'],
            ]);
        }

        $user->update([
            'otp' => null,
            'otp_expiration' => null,
            'connect' => 1,
        ]);

        RateLimiter::clear($this->rateLimitKey('otp-verify', $email));

        return $user->fresh();
    }

    protected function sendOtp(User $user, string $otp): void
    {
        $email = trim((string) $user->mail);

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => ['Adresse email invalide pour l\'envoi du code OTP.'],
            ]);
        }

        try {
            $user->notifyNow(new OtpCodeNotification($otp, $this->expiresMinutes()));
        } catch (\Throwable $e) {
            report($e);

            logger()->error('Échec envoi OTP par email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            if (config('mail.default') === 'log' && config('app.debug')) {
                logger()->info('OTP dev fallback (MAIL_MAILER=log)', [
                    'email' => $email,
                    'otp' => $otp,
                ]);

                return;
            }

            throw ValidationException::withMessages([
                'email' => ['Impossible d\'envoyer le code OTP. Vérifiez la configuration email ou réessayez plus tard.'],
            ]);
        }

        if (config('mail.default') === 'log' && config('app.debug')) {
            logger()->info('OTP enregistré dans les logs (MAIL_MAILER=log)', [
                'email' => $email,
                'otp' => $otp,
            ]);
        }
    }

    protected function generateCode(): string
    {
        $length = (int) config('chrononews.otp.length', 6);
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    protected function expiresMinutes(): int
    {
        return (int) config('chrononews.otp.expires_minutes', 10);
    }

    protected function ensureSendAllowed(string $email): void
    {
        $cooldownKey = $this->rateLimitKey('otp-cooldown', $email);

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);

            throw ValidationException::withMessages([
                'email' => ["Veuillez patienter {$seconds} secondes avant de renvoyer un code."],
            ]);
        }

        $hourlyKey = $this->rateLimitKey('otp-hourly', $email);
        $maxPerHour = (int) config('chrononews.otp.max_requests_per_hour', 10);

        if (RateLimiter::tooManyAttempts($hourlyKey, $maxPerHour)) {
            $seconds = RateLimiter::availableIn($hourlyKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            throw ValidationException::withMessages([
                'email' => ["Trop de demandes de code. Réessayez dans {$minutes} minute(s)."],
            ]);
        }
    }

    protected function recordSendAttempt(string $email): void
    {
        RateLimiter::hit($this->rateLimitKey('otp-cooldown', $email), $this->resendCooldownSeconds());
        RateLimiter::hit($this->rateLimitKey('otp-hourly', $email), 3600);
    }

    protected function resendCooldownSeconds(): int
    {
        return (int) config('chrononews.otp.resend_cooldown_seconds', 60);
    }

    protected function ensureNotRateLimited(string $action, string $email): void
    {
        if ($action !== 'otp-verify') {
            return;
        }

        $key = $this->rateLimitKey($action, $email);
        $maxAttempts = (int) config('chrononews.otp.max_attempts', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'otp' => ["Trop de tentatives. Réessayez dans {$seconds} secondes."],
            ]);
        }
    }

    protected function rateLimitKey(string $action, string $email): string
    {
        return Str::lower($action.':'.trim($email));
    }
}
