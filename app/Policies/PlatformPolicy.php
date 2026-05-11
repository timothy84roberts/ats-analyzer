<?php

namespace App\Policies;

use App\Models\Platform;
use App\Models\User;

class PlatformPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Platform $platform): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Platform $platform): bool
    {
        return true;
    }

    public function delete(User $user, Platform $platform): bool
    {
        return true;
    }
}
