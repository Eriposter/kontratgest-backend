<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Contracts\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('contracts.view');
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo('contracts.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('contracts.create');
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo('contracts.update');
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo('contracts.delete');
    }

    public function approve(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo('contracts.approve');
    }

    public function terminate(User $user, Contract $contract): bool
    {
        return $user->hasPermissionTo('contracts.terminate');
    }
}