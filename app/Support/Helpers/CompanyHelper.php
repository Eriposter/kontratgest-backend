<?php

declare(strict_types=1);

namespace App\Support\Helpers;

use App\Domain\Companies\Models\Company;

class CompanyHelper
{
    protected static ?Company $currentCompany = null;

    public static function setCurrentCompany(Company $company): void
    {
        static::$currentCompany = $company;
    }

    public static function getCurrentCompany(): ?Company
    {
        if (static::$currentCompany === null) {
            // Por agora, retornar a primeira empresa ativa
            // No futuro, isto virá do utilizador autenticado ou do subdomínio
            static::$currentCompany = Company::where('is_active', true)->first();
        }

        return static::$currentCompany;
    }

    public static function isPublic(): bool
    {
        $company = static::getCurrentCompany();
        return $company?->isPublic() ?? false;
    }

    public static function isPrivate(): bool
    {
        $company = static::getCurrentCompany();
        return $company?->isPrivate() ?? false;
    }

    public static function hasFeature(string $feature): bool
    {
        $company = static::getCurrentCompany();
        return $company?->hasFeature($feature) ?? false;
    }
}