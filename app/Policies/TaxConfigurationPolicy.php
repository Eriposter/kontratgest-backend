<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Tax\Models\TaxConfiguration;
use App\Models\User;

class TaxConfigurationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tax.view');
    }

    public function view(User $user, TaxConfiguration $tax): bool
    {
        return $user->hasPermissionTo('tax.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tax.create');
    }

    public function update(User $user, TaxConfiguration $tax): bool
    {
        return $user->hasPermissionTo('tax.update');
    }

    public function delete(User $user, TaxConfiguration $tax): bool
    {
        return $user->hasPermissionTo('tax.delete');
    }
}