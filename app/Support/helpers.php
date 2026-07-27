<?php

if (!function_exists('current_company')) {
    function current_company(): ?\App\Domain\Companies\Models\Company
    {
        return \App\Support\Helpers\CompanyHelper::getCurrentCompany();
    }
}

if (!function_exists('is_public_company')) {
    function is_public_company(): bool
    {
        return \App\Support\Helpers\CompanyHelper::isPublic();
    }
}

if (!function_exists('has_feature')) {
    function has_feature(string $feature): bool
    {
        return \App\Support\Helpers\CompanyHelper::hasFeature($feature);
    }
}