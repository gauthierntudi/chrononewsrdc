<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LegacySessionBridge
{
    private const SESSION_KEYS = [
        'logged_in',
        'user_id',
        'user_email',
        'user_name',
        'user_role',
    ];

    private static bool $standaloneBooted = false;

    public static function syncToLegacySession(): void
    {
        $user = Auth::user();
        if ($user instanceof User) {
            self::hydratePhpSession($user);
        }
    }

    public static function persistFromUser(User $user): void
    {
        self::hydratePhpSession($user);

        if (function_exists('session') && app()->bound('session')) {
            session(self::legacySessionPayload($user));
        }
    }

    public static function clear(): void
    {
        foreach (self::SESSION_KEYS as $key) {
            unset($_SESSION[$key]);
        }

        if (function_exists('session') && app()->bound('session')) {
            session()->forget(self::SESSION_KEYS);
        }
    }

    /**
     * Charge la session Laravel (cookie DB) pour les scripts legacy standalone
     * comme publication/api/payments.php.
     */
    public static function bootstrapStandalone(): void
    {
        if (function_exists('app')) {
            try {
                if (app()->bound('auth')) {
                    self::syncToLegacySession();

                    return;
                }
            } catch (\Throwable) {
                // Pas encore dans le cycle HTTP Laravel
            }
        }

        if (self::$standaloneBooted) {
            self::syncToLegacySession();

            return;
        }

        $base = self::laravelBasePath();
        if ($base === null) {
            return;
        }

        require_once $base.'/vendor/autoload.php';
        $app = require $base.'/bootstrap/app.php';

        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();

        $request = Request::capture();
        $app->instance('request', $request);
        $app->instance(\Illuminate\Http\Request::class, $request);

        (new \Illuminate\Routing\Pipeline($app))
            ->send($request)
            ->through([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
            ])
            ->then(static fn () => new \Illuminate\Http\Response());

        self::$standaloneBooted = true;
        self::syncToLegacySession();
    }

    private static function laravelBasePath(): ?string
    {
        $candidates = [
            dirname(__DIR__, 2),
            dirname(__DIR__, 3),
        ];

        foreach ($candidates as $path) {
            if (is_file($path.'/bootstrap/app.php')) {
                return $path;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private static function legacySessionPayload(User $user): array
    {
        $role = $user->role;
        $roleValue = $role instanceof \BackedEnum ? $role->value : (string) ($role ?? 'user');

        return [
            'logged_in' => true,
            'user_id' => (int) $user->id,
            'user_email' => (string) ($user->mail ?? ''),
            'user_name' => (string) ($user->nom ?? ''),
            'user_role' => $roleValue,
        ];
    }

    private static function hydratePhpSession(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        foreach (self::legacySessionPayload($user) as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }
}
