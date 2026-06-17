<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    /** @param  array<string, mixed>  $data */
    public function update(User $user, array $data): User
    {
        $map = [
            'nom' => 'nom',
            'telephone' => 'telephone',
            'titre' => 'Titre',
            'cover' => 'cover',
            'bio' => 'bio',
            'facebook' => 'Facebook',
            'youtube' => 'Youtube',
            'twitter' => 'Twitter',
            'instagram' => 'Instagram',
        ];

        $updates = [];

        foreach ($map as $input => $column) {
            if (array_key_exists($input, $data)) {
                $updates[$column] = $data[$input];
            }
        }

        if ($updates === []) {
            throw ValidationException::withMessages([
                'profile' => 'Aucune donnée à mettre à jour.',
            ]);
        }

        $user->update($updates);

        return $user->fresh();
    }
}
