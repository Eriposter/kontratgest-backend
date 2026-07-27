<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Base
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            CompanySeeder::class, // ← NOVO
            
            // Dados de negócio
            EntitySeeder::class,
            ContractTypeSeeder::class,
            TaxConfigurationSeeder::class,
            UraSeeder::class, // ← NOVO
            ContractProcedureSeeder::class, // ← NOVO
            ContractSeeder::class,
            GuaranteeSeeder::class,
            MeasurementSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}