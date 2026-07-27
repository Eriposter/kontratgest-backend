<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Payments\Models\Measurement;
use App\Models\User;

class MeasurementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('measurements.view');
    }

    public function view(User $user, Measurement $measurement): bool
    {
        return $user->hasPermissionTo('measurements.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('measurements.create');
    }

    public function update(User $user, Measurement $measurement): bool
    {
        return $user->hasPermissionTo('measurements.update');
    }

    public function delete(User $user, Measurement $measurement): bool
    {
        return $user->hasPermissionTo('measurements.delete');
    }

    public function approve(User $user, Measurement $measurement): bool
    {
        return $user->hasPermissionTo('measurements.approve');
    }
}