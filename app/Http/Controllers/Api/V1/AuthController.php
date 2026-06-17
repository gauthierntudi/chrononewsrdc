<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use App\Services\Auth\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otp,
        protected ProfileService $profile,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $result = $this->otp->register($data['nom'], $data['email']);

        return response()->json($result);
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $result = $this->otp->requestLoginOtp($data['email']);

        return response()->json($result);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $result = $this->otp->resendLoginOtp($data['email']);

        return response()->json($result);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:'.config('chrononews.otp.length', 6)],
        ]);

        $user = $this->otp->verify($data['email'], $data['otp']);

        Auth::login($user);
        $request->session()->regenerate();
        \App\Support\LegacySessionBridge::persistFromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'has_active_subscription' => $user->hasActiveSubscription(),
            'user' => $user->toAuthArray(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        \App\Support\LegacySessionBridge::clear();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non connecté'], 401);
        }

        return response()->json([
            'success' => true,
            'logged_in' => true,
            'is_superadmin' => $user->isSuperAdmin(),
            'user' => $user->toAuthArray(),
        ]);
    }

    public function checkSession(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'logged_in' => (bool) $user,
            'is_superadmin' => $user?->isSuperAdmin() ?? false,
            'user' => $user?->toAuthArray(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non connecté'], 401);
        }

        $data = $request->validate([
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'titre' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:200'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'youtube' => ['nullable', 'string', 'max:500'],
            'twitter' => ['nullable', 'string', 'max:500'],
            'instagram' => ['nullable', 'string', 'max:500'],
        ]);

        $updated = $this->profile->update($user, $data);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => $updated->toAuthArray(),
        ]);
    }
}
