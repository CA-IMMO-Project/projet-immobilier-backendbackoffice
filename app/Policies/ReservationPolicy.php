<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    private function estAdmin(User $user): bool
    {
        return $user->actif && $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $this->estAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->estAdmin($user);
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $this->estAdmin($user);
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $this->estAdmin($user);
    }
}
