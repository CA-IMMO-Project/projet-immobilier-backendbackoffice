<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Règle commune : seul un compte admin actif (Back Office) peut gérer les clients.
     * Note : $user est ici un App\Models\User (l'admin), pas le Client concerné.
     */
    private function estAdmin(User $user): bool
    {
        return $user->actif && $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function view(User $user, Client $client): bool
    {
        return $this->estAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->estAdmin($user);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->estAdmin($user);
    }

    public function restore(User $user, Client $client): bool
    {
        return $this->estAdmin($user);
    }
}