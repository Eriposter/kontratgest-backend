<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_contract_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->integer('year');
            $table->string('title');
            $table->text('description')->nullable();
            
            // Valores
            $table->decimal('total_planned_amount', 15, 2)->default(0);
            $table->decimal('total_executed_amount', 15, 2)->default(0);
            
            // Status
            $table->string('status', 20)->default('draft'); // draft, submitted, approved, in_progress, completed, cancelled
            
            // Aprovação
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Auditoria
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id', 'year']); // Um PAC por ano por empresa
            $table->index(['company_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_contract_plans');
    }
};