<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('measurement_id');
            
            // Item do auto de medição
            $table->string('item_code', 50)->nullable(); // Código do item (ex: "01.02.03")
            $table->string('description');
            $table->string('unit', 30); // m, m², m³, kg, un, h, etc.
            $table->decimal('quantity', 15, 4); // Quantidade executada
            $table->decimal('unit_price', 15, 2); // Preço unitário
            $table->decimal('total_amount', 15, 2); // quantity * unit_price
            
            // Dados específicos (JSONB)
            $table->jsonb('specific_data')->default('{}');
            // Ex: {"localizacao": "Piso 1", "observacoes": "Conforme projeto"}
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('measurement_id')
                  ->references('id')->on('measurements')
                  ->cascadeOnDelete();
            
            // Índices
            $table->index(['measurement_id', 'item_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_items');
    }
};