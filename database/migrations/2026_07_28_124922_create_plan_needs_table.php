<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_needs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_id');
            
            // Tipo de contrato
            $table->string('contract_type', 30); // works, goods, services, consultancy
            
            // Tipo de procedimento (legislação angolana)
            $table->string('procedure_type', 30); // dynamic_electronic, invitation, limited_tender, direct_award
            
            // Dados
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('justification')->nullable();
            
            // Valores
            $table->decimal('estimated_amount', 15, 2);
            $table->decimal('executed_amount', 15, 2)->nullable();
            
            // Ligação ao contrato gerado
            $table->uuid('contract_id')->nullable();
            
            // Planeamento
            $table->string('priority', 10)->default('medium'); // high, medium, low
            $table->integer('planned_quarter')->nullable(); // 1-4
            $table->string('status', 20)->default('planned'); // planned, in_progress, contracted, cancelled
            
            $table->timestamps();
            
            $table->foreign('plan_id')->references('id')->on('annual_contract_plans')->cascadeOnDelete();
            $table->foreign('contract_id')->references('id')->on('contracts')->nullOnDelete();
            $table->index(['plan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_needs');
    }
};