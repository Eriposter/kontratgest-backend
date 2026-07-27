<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identificação
            $table->string('contract_number', 50)->unique(); // EMP/2026/00123
            $table->uuid('contract_type_id');
            $table->uuid('counterparty_id'); // FK -> entities
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('object')->nullable(); // Objeto do contrato
            
            // Financeiro
            $table->string('currency', 3)->default('AOA'); // AOA, USD, EUR
            $table->decimal('total_amount', 15, 2);
            $table->decimal('vat_rate', 5, 2)->default(14.00); // IVA Angola
            $table->decimal('withholding_tax_rate', 5, 2)->default(0.00); // Retenção na fonte
            $table->decimal('exchange_rate', 10, 6)->nullable(); // Taxa BNA
            
            // Datas
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('signature_date')->nullable();
            $table->integer('duration_months')->nullable();
            
            // Modelo de pagamento
            $table->string('payment_model', 20); // single, installment, measurement, consignment
            
            // Compliance Angola
            $table->boolean('requires_bna_registration')->default(false);
            $table->string('bna_registration_number', 50)->nullable();
            $table->date('bna_registration_date')->nullable();
            
            $table->boolean('tribunal_de_contas_visto')->default(false);
            $table->string('tribunal_visto_number', 50)->nullable();
            $table->date('tribunal_visto_date')->nullable();
            
            // Dados específicos por tipo (JSONB)
            $table->jsonb('specific_data')->default('{}');
            /*
             * Exemplos:
             * - Empreitada: {"prazo_garantia_obra": 24, "rececao_provisoria": "2026-12-31"}
             * - Serviço: {"slas": [...], "horas_mensais": 160}
             * - Fornecimento: {"lotes": [...], "entregas_parciais": true}
             */
            
            // Estado e workflow
            $table->string('status', 20)->default('draft');
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('contract_type_id')
                  ->references('id')->on('contract_types')
                  ->restrictOnDelete();
            
            $table->foreign('counterparty_id')
                  ->references('id')->on('entities')
                  ->restrictOnDelete();
            
            // Índices
            $table->index(['status', 'created_at']);
            $table->index(['counterparty_id', 'status']);
            $table->index('contract_number');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};