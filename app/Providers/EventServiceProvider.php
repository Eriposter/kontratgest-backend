<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Eventos futuros:
        // ContractSubmittedForApproval::class => [
        //     NotifyApprovers::class,
        // ],
        // GuaranteeExpiringSoon::class => [
        //     SendGuaranteeExpiryAlert::class,
        // ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}