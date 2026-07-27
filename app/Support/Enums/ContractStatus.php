<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum ContractStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case TERMINATED = 'terminated';
    case EXPIRED = 'expired';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::PENDING_APPROVAL => 'Pendente de Aprovação',
            self::APPROVED => 'Aprovado',
            self::ACTIVE => 'Ativo',
            self::SUSPENDED => 'Suspenso',
            self::TERMINATED => 'Rescindido',
            self::EXPIRED => 'Expirado',
            self::ARCHIVED => 'Arquivado',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        // Usar valores string explicitamente para evitar problemas de conversão
        $transitions = [
            'draft' => ['pending_approval'],
            'pending_approval' => ['approved', 'draft'],
            'approved' => ['active', 'terminated'],
            'active' => ['suspended', 'terminated', 'expired'],
            'suspended' => ['active', 'terminated'],
            'terminated' => ['archived'],
            'expired' => ['archived'],
            'archived' => [],
        ];

        return in_array($target->value, $transitions[$this->value] ?? [], true);
    }
}