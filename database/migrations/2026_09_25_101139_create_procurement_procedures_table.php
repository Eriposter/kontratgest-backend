<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_need_id')->constrained('plan_needs')->cascadeOnDelete();
            $table->uuid('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->uuid('winning_entity_id')->nullable()->constrained('entities')->nullOnDelete();
            
            // Tipo de procedimento
            $table->enum('procedure_type', ['cp', 'clpq', 'clc', 'pcs', 'pde', 'pce']);
            
            // Datas
            $table->date('procedure_start_date');
            $table->date('procedure_end_date');
            $table->date('confirmed_start_date')->nullable();
            $table->date('confirmed_end_date')->nullable();
            
            // Estado
            $table->enum('status', ['planned', 'in_progress', 'evaluation', 'completed', 'cancelled', 'failed'])->default('planned');
            
            // Valores
            $table->decimal('estimated_amount', 15, 2)->nullable();
            $table->decimal('contracted_amount', 15, 2)->nullable();
            
            // Documentos e notas
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->text('adjudication_notes')->nullable();
            
            // Auditoria
            $table->uuid('created_by')->nullable();
            $table->uuid('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_procedures');
    }
};