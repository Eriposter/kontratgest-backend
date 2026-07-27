<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guarantees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            
            // Identificação
            $table->string('guarantee_number', 50)->unique(); // GB/2026/00123
            $table->string('guarantee_type', 30); // bank_guarantee, insurance, cash_deposit, promissory_note
            $table->string('purpose', 30); // bid, performance, advance_payment, retention, warranty
            
            // Entidade emissora
            $table->string('issuing_entity'); // "BAI", "Allianz Angola", "BFA"
            $table->string('issuing_entity_nif', 14)->nullable();
            $table->string('issuing_entity_contact')->nullable();
            
            // Valores
            $table->string('currency', 3)->default('AOA');
            $table->decimal('amount', 15, 2);
            $table->decimal('exchange_rate', 10, 6)->nullable(); // Se moeda estrangeira
            
            // Datas
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->integer('validity_days')->nullable(); // Calculado automaticamente
            
            // Condições de libertação
            $table->text('release_conditions')->nullable();
            $table->date('release_date')->nullable();
            $table->uuid('released_by')->nullable();
            $table->text('release_notes')->nullable();
            
            // Execução (se a caução foi executada)
            $table->boolean('was_executed')->default(false);
            $table->decimal('executed_amount', 15, 2)->nullable();
            $table->date('executed_at')->nullable();
            $table->text('execution_reason')->nullable();
            
            // Estado
            $table->string('status', 20)->default('active'); // active, released, expired, executed, cancelled
            
            // Documentos
            $table->string('document_reference')->nullable(); // Referência do documento físico
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('contract_id')
                  ->references('id')->on('contracts')
                  ->cascadeOnDelete();
            
            // Índices
            $table->index(['contract_id', 'status']);
            $table->index('expiry_date');
            $table->index(['status', 'expiry_date']);
            $table->index('guarantee_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guarantees');
    }
};