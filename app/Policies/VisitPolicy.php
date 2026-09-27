<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    private function estAdmin(User $user): bool
    {
        return $user->actif && $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function view(User $user, Visit $visit): bool
    {
        return $this->estAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function update(User $user, Visit $visit): bool
    {
        return $this->estAdmin($user);
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $this->estAdmin($user);
    }
}
