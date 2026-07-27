<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('code', 30)->unique(); // SERVICE, WORKS, SUPPLY, LEASE
            $table->string('name'); // "Empreitada de Obras Públicas"
            $table->text('description')->nullable();
            
            // Configurações padrão para este tipo
            $table->jsonb('default_payment_terms')->default('{}');
            // Ex: {"installments": 3, "first_payment_percentage": 30}
            
            $table->jsonb('required_guarantees')->default('[]');
            // Ex: ["performance", "advance_payment"]
            
            $table->jsonb('specific_fields_schema')->default('{}');
            // Define campos extra específicos para este tipo
            
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_types');
    }
};