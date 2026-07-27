<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_configurations', function (Blueprint $table) {
            // Adicionar apenas se a coluna ainda não existir (segurança)
            if (!Schema::hasColumn('tax_configurations', 'company_id')) {
                $table->uuid('company_id')->nullable()->after('id');
                $table->foreign('company_id')
                      ->references('id')->on('companies')
                      ->cascadeOnDelete();
                $table->index('company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_configurations', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};