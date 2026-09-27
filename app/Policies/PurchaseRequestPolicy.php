<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    private function estAdmin(User $user): bool
    {
        return $user->actif && $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->estAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->estAdmin($user);
    }

    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->estAdmin($user);
    }
}
