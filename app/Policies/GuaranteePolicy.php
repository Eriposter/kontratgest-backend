<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Guarantees\Models\Guarantee;
use App\Models\User;

class GuaranteePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('guarantees.view');
    }

    public function view(User $user, Guarantee $guarantee): bool
    {
        return $user->hasPermissionTo('guarantees.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('guarantees.create');
    }

    public function update(User $user, Guarantee $guarantee): bool
    {
        return $user->hasPermissionTo('guarantees.update');
    }

    public function delete(User $user, Guarantee $guarantee): bool
    {
        return $user->hasPermissionTo('guarantees.delete');
    }

    public function release(User $user, Guarantee $guarantee): bool
    {
        return $user->hasPermissionTo('guarantees.release');
    }

    public function execute(User $user, Guarantee $guarantee): bool
    {
        return $user->hasPermissionTo('guarantees.execute');
    }
}