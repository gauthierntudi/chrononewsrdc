<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    public function paginate(int $page = 1, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = User::query()->orderByDesc('id');

        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('nom', 'like', $term)
                    ->orWhere('mail', 'like', $term)
                    ->orWhere('telephone', 'like', $term);
            });
        }

        return $query->paginate(
            perPage: min($perPage, 50),
            page: max($page, 1),
        );
    }

    public function toggleStatus(int $userId): array
    {
        $user = User::query()->findOrFail($userId);

        if ($user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'user_id' => ['Un super administrateur ne peut pas être désactivé.'],
            ]);
        }

        $newStatus = $user->isActive() ? 0 : 1;
        $user->update(['status' => $newStatus]);

        return [
            'success' => true,
            'message' => 'Utilisateur '.($newStatus === 1 ? 'activé' : 'désactivé').' avec succès',
            'new_status' => $newStatus,
        ];
    }

    public function create(array $data): array
    {
        $validated = Validator::make($data, [
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,mail'],
            'role' => ['sometimes', 'string', 'in:user,journaliste,admin,superadmin'],
            'titre' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string'],
        ])->validate();

        User::query()->create([
            'nom' => $validated['nom'],
            'mail' => $validated['email'],
            'role' => $validated['role'] ?? UserRole::User->value,
            'num_user' => 'USR'.time().random_int(1000, 9999),
            'status' => 1,
            'Titre' => $validated['titre'] ?? '',
            'telephone' => $validated['telephone'] ?? '',
            'bio' => $validated['bio'] ?? '',
            'cover' => '',
            'mdp' => '',
        ]);

        return [
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
        ];
    }

    public function updateRole(int $userId, string $role): array
    {
        if (! in_array($role, ['user', 'journaliste', 'admin', 'superadmin'], true)) {
            throw ValidationException::withMessages(['role' => ['Rôle invalide']]);
        }

        $user = User::query()->findOrFail($userId);

        if ($user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'role' => ['Le rôle d\'un super administrateur ne peut pas être modifié.'],
            ]);
        }

        $user->update(['role' => $role]);

        return [
            'success' => true,
            'message' => 'Rôle modifié avec succès',
            'new_role' => $role,
        ];
    }

    public function find(int $userId): User
    {
        return User::query()->findOrFail($userId);
    }

    public function update(int $userId, array $data, User $actor): array
    {
        $user = User::query()->findOrFail($userId);

        $validated = Validator::make($data, [
            'nom' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'mail')->ignore($userId)],
            'telephone' => ['nullable', 'string', 'max:50'],
            'titre' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'role' => ['sometimes', 'string', 'in:user,journaliste,admin,superadmin'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'string', 'max:500'],
        ])->validate();

        $isSelf = $actor->id === $user->id;
        $targetIsSuperAdmin = $user->isSuperAdmin();

        if (array_key_exists('role', $validated)) {
            if ($isSelf && $validated['role'] !== UserRole::SuperAdmin->value) {
                throw ValidationException::withMessages([
                    'role' => ['Vous ne pouvez pas modifier votre propre rôle super admin.'],
                ]);
            }

            if ($targetIsSuperAdmin && $validated['role'] !== UserRole::SuperAdmin->value) {
                throw ValidationException::withMessages([
                    'role' => ['Le rôle d\'un super administrateur ne peut pas être modifié.'],
                ]);
            }
        }

        if (array_key_exists('status', $validated)) {
            if ($isSelf && (int) $validated['status'] === 0) {
                throw ValidationException::withMessages([
                    'status' => ['Vous ne pouvez pas désactiver votre propre compte.'],
                ]);
            }

            if ($targetIsSuperAdmin && (int) $validated['status'] === 0) {
                throw ValidationException::withMessages([
                    'status' => ['Un super administrateur ne peut pas être désactivé.'],
                ]);
            }
        }

        $updates = [];

        if (array_key_exists('nom', $validated)) {
            $updates['nom'] = $validated['nom'];
        }
        if (array_key_exists('email', $validated)) {
            $updates['mail'] = $validated['email'];
        }
        if (array_key_exists('telephone', $validated)) {
            $updates['telephone'] = $validated['telephone'] ?? '';
        }
        if (array_key_exists('titre', $validated)) {
            $updates['Titre'] = $validated['titre'] ?? '';
        }
        if (array_key_exists('bio', $validated)) {
            $updates['bio'] = $validated['bio'] ?? '';
        }
        if (array_key_exists('role', $validated) && ! $targetIsSuperAdmin) {
            $updates['role'] = $validated['role'];
        }
        if (array_key_exists('status', $validated) && ! ($targetIsSuperAdmin && (int) $validated['status'] === 0)) {
            $updates['status'] = (int) $validated['status'];
        }
        if (array_key_exists('facebook', $validated)) {
            $updates['Facebook'] = $validated['facebook'] ?? '';
        }
        if (array_key_exists('youtube', $validated)) {
            $updates['Youtube'] = $validated['youtube'] ?? '';
        }
        if (array_key_exists('twitter', $validated)) {
            $updates['Twitter'] = $validated['twitter'] ?? '';
        }
        if (array_key_exists('instagram', $validated)) {
            $updates['Instagram'] = $validated['instagram'] ?? '';
        }
        if (array_key_exists('cover', $validated)) {
            $updates['cover'] = $validated['cover'] ?? '';
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        return [
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user->fresh()->toAdminArray(),
        ];
    }

    public function delete(int $userId, User $actor): array
    {
        $user = User::query()->findOrFail($userId);

        if ($actor->id === $user->id) {
            throw ValidationException::withMessages([
                'user_id' => ['Vous ne pouvez pas supprimer votre propre compte.'],
            ]);
        }

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'user_id' => ['Seul un super administrateur peut supprimer un autre super administrateur.'],
            ]);
        }

        $articleCount = $this->countUserArticles($user->id);
        if ($articleCount > 0) {
            throw ValidationException::withMessages([
                'user_id' => ["Cet utilisateur possède {$articleCount} article(s). Supprimez-les d'abord ou désactivez le compte."],
            ]);
        }

        DB::transaction(function () use ($user): void {
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            $user->subscriptions()->delete();
            $user->articlePurchases()->delete();
            $user->advertisements()->delete();
            $user->payments()->delete();
            $user->delete();
        });

        return [
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès',
        ];
    }

    private function countUserArticles(int $userId): int
    {
        $count = 0;

        if (Schema::hasTable('actualites')) {
            $count += (int) DB::table('actualites')->where('id_redaction', $userId)->count();
        }

        if (Schema::hasTable('articles')) {
            $count += (int) DB::table('articles')->where('user_id', $userId)->count();
        }

        return $count;
    }
}
