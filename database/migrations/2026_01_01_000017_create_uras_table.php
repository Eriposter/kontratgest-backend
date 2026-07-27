<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            
            $table->string('name'); // ex: "URA de Obras Públicas"
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            
            // Membros da URA
            $table->jsonb('members')->default('[]');
            /*
             * Exemplo:
             * [
             *   {"name": "João Silva", "role": "presidente", "user_id": "uuid"},
             *   {"name": "Maria Santos", "role": "vogal", "user_id": "uuid"},
             *   {"name": "Pedro Costa", "role": "secretário", "user_id": "uuid"}
             * ]
             */
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uras');
    }
};