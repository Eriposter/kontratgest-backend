<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar company_id a todas as tabelas principais
        $tables = [
            'entities',
            'contracts',
            'guarantees',
            'measurements',
            'payments',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->uuid('company_id')->nullable()->after('id');
                $table->foreign('company_id')
                      ->references('id')->on('companies')
                      ->nullOnDelete();
                $table->index('company_id');
            });
        }
    }

    public function down(): void
    {
        $tables = ['entities', 'contracts', 'guarantees', 'measurements', 'payments'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};