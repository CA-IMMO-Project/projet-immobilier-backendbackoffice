<?php

namespace App\Policies;

use App\Models\Terrain;
use App\Models\User;

class TerrainPolicy
{
    private function estAdmin(User $user): bool
    {
        return $user->actif && $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function view(User $user, Terrain $terrain): bool
    {
        return $this->estAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function update(User $user, Terrain $terrain): bool
    {
        return $this->estAdmin($user);
    }

    public function delete(User $user, Terrain $terrain): bool
    {
        return $this->estAdmin($user);
    }

    public function restore(User $user, Terrain $terrain): bool
    {
        return $this->estAdmin($user);
    }
}
