<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $types = [
            [
                'code' => 'public_works',
                'name' => 'Empreitada de obras públicas',
                'description' => 'Construção, reconstrução, ampliação e conservação de obras públicas',
                'is_active' => true,
            ],
            [
                'code' => 'goods_acquisition',
                'name' => 'Aquisição de bens móveis',
                'description' => 'Compra de equipamentos, materiais e outros bens móveis',
                'is_active' => true,
            ],
            [
                'code' => 'services_acquisition',
                'name' => 'Aquisição de serviços',
                'description' => 'Prestação de serviços diversos',
                'is_active' => true,
            ],
            [
                'code' => 'consultancy',
                'name' => 'Serviços de consultoria',
                'description' => 'Serviços de consultoria técnica e especializada',
                'is_active' => true,
            ],
            [
                'code' => 'goods_rental',
                'name' => 'Locação de bens móveis',
                'description' => 'Aluguer de equipamentos e outros bens móveis',
                'is_active' => true,
            ],
            [
                'code' => 'public_works_concession',
                'name' => 'Concessão de obras públicas',
                'description' => 'Concessão para construção e exploração de obras públicas',
                'is_active' => true,
            ],
            [
                'code' => 'public_services_concession',
                'name' => 'Concessão de serviços públicos',
                'description' => 'Concessão para exploração de serviços públicos',
                'is_active' => true,
            ],
            [
                'code' => 'other',
                'name' => 'Outro',
                'description' => 'Outros tipos de contrato não previstos',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            DB::table('contract_types')->insert([
                'id' => (string) Str::uuid(),
                'code' => $type['code'],
                'name' => $type['name'],
                'description' => $type['description'] ?? null,
                'is_active' => $type['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('contract_types')->whereIn('code', [
            'public_works',
            'goods_acquisition',
            'services_acquisition',
            'consultancy',
            'goods_rental',
            'public_works_concession',
            'public_services_concession',
            'other',
        ])->delete();
    }
};