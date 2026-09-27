<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Règle commune : seul un compte admin actif peut gérer les utilisateurs.
     */
    private function estAdmin(User $user): bool
    {
        return $user->actif && $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function view(User $user, User $cible): bool
    {
        return $this->estAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function update(User $user, User $cible): bool
    {
        return $this->estAdmin($user);
    }

    public function delete(User $user, User $cible): bool
    {
        // Un admin ne peut pas se supprimer lui-même par erreur via l'API
        return $this->estAdmin($user) && $user->id !== $cible->id;
    }

    public function restore(User $user, User $cible): bool
    {
        return $this->estAdmin($user);
    }
}