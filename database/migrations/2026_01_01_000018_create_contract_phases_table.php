<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_phases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            
            $table->string('phase_type', 30); // pre_contractual, adjudication, signature, tribunal_visto, execution, provisional_receipt, definitive_receipt, closure
            $table->string('phase_name');
            $table->text('description')->nullable();
            
            $table->string('status', 20)->default('pending'); // pending, in_progress, completed, blocked
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('deadline')->nullable();
            
            // Para Tribunal de Contas
            $table->string('visto_number', 50)->nullable();
            $table->date('visto_date')->nullable();
            $table->text('visto_observations')->nullable();
            
            // Para publicações
            $table->string('publication_number', 50)->nullable();
            $table->date('publication_date')->nullable();
            $table->string('diary_series', 10)->nullable(); // I, II
            
            // Para receções
            $table->date('receipt_date')->nullable();
            $table->text('receipt_observations')->nullable();
            $table->jsonb('receipt_defects')->default('[]'); // Defeitos encontrados
            
            $table->uuid('responsible_user_id')->nullable();
            
            $table->timestamps();
            
            $table->foreign('contract_id')->references('id')->on('contracts')->cascadeOnDelete();
            $table->index(['contract_id', 'phase_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_phases');
    }
};