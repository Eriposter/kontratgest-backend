<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            
            $table->string('procedure_number', 50)->unique(); // ex: "CP/2026/001"
            $table->string('procedure_type', 30); // public_tender, limited_tender, direct_contract, prior_consultation
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('legal_basis', 100)->nullable(); // Base legal (ex: "Artigo 45º da Lei 41/20")
            $table->string('justification')->nullable(); // Justificação do procedimento
            
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('currency', 3)->default('AOA');
            
            $table->date('publication_date')->nullable();
            $table->date('proposal_deadline')->nullable();
            $table->date('adjudication_date')->nullable();
            
            $table->string('status', 20)->default('draft'); // draft, published, evaluation, adjudicated, cancelled
            
            $table->jsonb('participants')->default('[]'); // Entidades que participaram
            $table->jsonb('evaluation_criteria')->default('{}'); // Critérios de avaliação
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['company_id', 'status']);
            $table->index('procedure_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_procedures');
    }
};