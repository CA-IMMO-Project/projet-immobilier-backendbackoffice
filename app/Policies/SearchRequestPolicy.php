<?php

namespace App\Policies;

use App\Models\SearchRequest;
use App\Models\User;

class SearchRequestPolicy
{
    /**
     * Règle commune : seul un compte admin actif (Back Office) peut gérer les demandes de recherche.
     */
    private function estAdmin(User $user): bool
    {
        return $user->actif && $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function view(User $user, SearchRequest $searchRequest): bool
    {
        return $this->estAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function update(User $user, SearchRequest $searchRequest): bool
    {
        return $this->estAdmin($user);
    }

    public function delete(User $user, SearchRequest $searchRequest): bool
    {
        return $this->estAdmin($user);
    }
}
