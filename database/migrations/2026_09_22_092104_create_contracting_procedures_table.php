<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracting_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_need_id')->constrained('plan_needs')->cascadeOnDelete();
            $table->uuid('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->uuid('winning_entity_id')->nullable()->constrained('entities')->nullOnDelete();
            
            // Datas do procedimento
            $table->date('procedure_start_date');
            $table->date('procedure_end_date');
            $table->date('confirmed_start_date')->nullable();
            $table->date('confirmed_end_date')->nullable();
            
            // Estado
            $table->enum('status', [
                'planned',           // Agendado
                'in_progress',       // Em curso (publicado)
                'evaluation',        // Em avaliação de propostas
                'completed',         // Finalizado (adjudicado)
                'cancelled',         // Cancelado
                'failed',            // Deserto / sem propostas
            ])->default('planned');
            
            // Documentos comprovativos (edital, acta de adjudicação, etc.)
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
        Schema::dropIfExists('contracting_procedures');
    }
};