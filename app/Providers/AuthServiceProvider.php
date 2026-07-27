<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Entities\Models\Entity;
use App\Domain\Guarantees\Models\Guarantee;
use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\Payment;
use App\Domain\Tax\Models\TaxConfiguration;
use App\Models\User;
use App\Policies\ContractPolicy;
use App\Policies\EntityPolicy;
use App\Policies\GuaranteePolicy;
use App\Policies\MeasurementPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\TaxConfigurationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Entity::class => EntityPolicy::class,
        Contract::class => ContractPolicy::class,
        Guarantee::class => GuaranteePolicy::class,
        Measurement::class => MeasurementPolicy::class,
        Payment::class => PaymentPolicy::class,
        TaxConfiguration::class => TaxConfigurationPolicy::class,
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // ─── Gate: Super Admin (acesso total) ───────────────
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }

            return null;
        });
    }
}