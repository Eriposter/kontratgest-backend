<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Payments\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('payments.create');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.update');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.delete');
    }

    public function approve(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.approve');
    }
}