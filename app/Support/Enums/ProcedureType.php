<?php

namespace App\Support\Enums;

enum ProcedureType: string
{
    case CP = 'cp'; // Concurso Público
    case CLPQ = 'clpq'; // Concurso Limitado por Prévia Qualificação
    case CLC = 'clc'; // Concurso Limitado por Convite
    case PCS = 'pcs'; // Procedimento de Contratação Simplificada
    case PDE = 'pde'; // Procedimento Dinâmico Eletrónico
    case PCE = 'pce'; // Procedimento de Contratação Emergencial

    public function label(): string
    {
        return match($this) {
            self::CP => 'Concurso Público',
            self::CLPQ => 'Concurso Limitado por Prévia Qualificação',
            self::CLC => 'Concurso Limitado por Convite',
            self::PCS => 'Procedimento de Contratação Simplificada',
            self::PDE => 'Procedimento Dinâmico Eletrónico',
            self::PCE => 'Procedimento de Contratação Emergencial',
        };
    }

    public function phases(): array
    {
        return match($this) {
            self::CP => [
                'Anúncio', 'Acto Público', 'Análise e Avaliação', 'Negociação', 'Adjudicação', 'Celebração do Contrato'
            ],
            self::CLPQ => [
                'Anúncio', 'Qualificação', 'Convite', 'Análise e Avaliação', 'Adjudicação', 'Celebração do Contrato'
            ],
            self::CLC => [
                'Convite', 'Análise e Avaliação', 'Negociação', 'Adjudicação', 'Celebração do Contrato'
            ],
            self::PCS => [
                'Convite', 'Análise e Avaliação', 'Negociação', 'Adjudicação', 'Celebração do Contrato'
            ],
            self::PDE => [
                'Anúncio', 'Leilão Eletrónico', 'Adjudicação', 'Celebração do Contrato'
            ],
            self::PCE => [
                'Solicitação Emergencial', 'Análise', 'Adjudicação', 'Celebração do Contrato'
            ],
        };
    }
}