<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_phases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procedure_id')->constrained('procurement_procedures')->cascadeOnDelete();
            
            // Fase
            $table->string('phase_name');
            $table->integer('sequence_order');
            $table->enum('status', ['pending', 'ongoing', 'completed', 'cancelled'])->default('pending');
            
            // Datas
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Documentos da fase
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->text('observations')->nullable();
            
            // Auditoria
            $table->uuid('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_phases');
    }
};