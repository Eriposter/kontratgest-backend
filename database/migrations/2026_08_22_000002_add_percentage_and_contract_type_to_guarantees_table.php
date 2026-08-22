<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guarantees', function (Blueprint $table) {
            // Adicionar company_id para melhor gestão
            $table->uuid('company_id')->nullable()->after('contract_id');
            
            // Campos para mostrar o valor da caução calculado
            $table->decimal('percentage_rate', 5, 2)->nullable()->after('amount');
            
            // Tipo de contrato para cálculo automático
            $table->string('contract_type', 30)->nullable()->after('purpose');
            
            $table->foreign('company_id')
                  ->references('id')->on('companies')
                  ->nullOnDelete();
                  
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('guarantees', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropColumn(['company_id', 'percentage_rate', 'contract_type']);
        });
    }
};
