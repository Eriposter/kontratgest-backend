<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Tipo de imposto
            $table->string('tax_type', 30); // iva, industrial, stamp_duty, withholding
            $table->string('name'); // "IVA - Taxa Normal"
            $table->text('description')->nullable();
            
            // Taxa
            $table->decimal('rate', 5, 2); // 14.00, 6.50, etc.
            
            // Regras de aplicação
            $table->jsonb('applicable_rules')->default('{}');
            /*
             * Exemplos:
             * - IVA: {"is_exempt": false, "applies_to": ["goods", "services"]}
             * - IIT: {"min_amount": 200000, "applies_to": ["services"], "entity_type": "individual"}
             * - Imposto de Selo: {"applies_to": ["contracts"], "min_amount": 1000000}
             */
            
            // Validade
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Índices
            $table->index(['tax_type', 'is_active']);
            $table->index(['valid_from', 'valid_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_configurations');
    }
};