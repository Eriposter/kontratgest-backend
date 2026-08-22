<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guarantees', function (Blueprint $table) {
            // Campos para mostrar o valor da caução calculado
            $table->decimal('percentage_rate', 5, 2)->nullable()->after('amount');
            
            // Tipo de contrato para cálculo automático
            $table->string('contract_type', 30)->nullable()->after('purpose');
        });
    }

    public function down(): void
    {
        Schema::table('guarantees', function (Blueprint $table) {
            $table->dropColumn(['percentage_rate', 'contract_type']);
        });
    }
};
