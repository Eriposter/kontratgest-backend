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
            ContractProcedureSeeder::class,
            ContractTypeSeeder::class,
            ContractSeeder::class,
            GuaranteeSeeder::class,
            PACSeeder::class,
            MeasurementSeeder::class,
            PaymentSeeder::class,
            TaxConfigurationSeeder::class,
            UraSeeder::class, // ← NOVO
        ]);
    }
}