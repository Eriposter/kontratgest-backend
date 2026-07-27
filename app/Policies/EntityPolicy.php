<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Entities\Models\Entity;
use App\Models\User;

class EntityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('entities.view');
    }

    public function view(User $user, Entity $entity): bool
    {
        return $user->hasPermissionTo('entities.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('entities.create');
    }

    public function update(User $user, Entity $entity): bool
    {
        return $user->hasPermissionTo('entities.update');
    }

    public function delete(User $user, Entity $entity): bool
    {
        return $user->hasPermissionTo('entities.delete');
    }
}