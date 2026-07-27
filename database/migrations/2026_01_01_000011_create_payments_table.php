<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            $table->uuid('payment_schedule_id')->nullable(); // Se associado a um marco
            
            // Identificação
            $table->string('payment_number', 50)->unique(); // PAG/2026/00123
            $table->string('payment_type', 30); // invoice, advance, measurement, milestone, final
            
            // Valores brutos
            $table->string('currency', 3)->default('AOA');
            $table->decimal('gross_amount', 15, 2); // Valor antes de impostos
            $table->decimal('exchange_rate', 10, 6)->nullable();
            
            // Impostos (Angola)
            $table->decimal('vat_rate', 5, 2)->default(14.00);
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            
            $table->decimal('withholding_tax_rate', 5, 2)->default(0.00); // IIT/IRS
            $table->decimal('withholding_tax_amount', 15, 2)->default(0.00);
            
            $table->decimal('stamp_duty_rate', 5, 2)->default(0.00); // Imposto de Selo
            $table->decimal('stamp_duty_amount', 15, 2)->default(0.00);
            
            $table->decimal('retention_amount', 15, 2)->default(0.00); // Retenção de garantia
            
            // Valor líquido
            $table->decimal('net_amount', 15, 2); // Valor a pagar
            
            // Datas
            $table->date('due_date')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('payment_date')->nullable();
            
            // Referência bancária
            $table->string('bank_reference', 100)->nullable();
            $table->string('payment_method', 30)->nullable(); // transfer, check, cash
            
            // Estado
            $table->string('status', 20)->default('pending'); // pending, approved, paid, rejected, cancelled
            $table->text('payment_notes')->nullable();
            
            // Aprovação
            $table->uuid('requested_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Documentos
            $table->string('invoice_number', 100)->nullable(); // Número da fatura do fornecedor
            $table->jsonb('supporting_documents')->default('[]'); // URLs de faturas, guias, etc.
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('contract_id')
                  ->references('id')->on('contracts')
                  ->cascadeOnDelete();
            
            $table->foreign('payment_schedule_id')
                  ->references('id')->on('payment_schedules')
                  ->nullOnDelete();
            
            // Índices
            $table->index(['contract_id', 'status']);
            $table->index('payment_number');
            $table->index(['status', 'due_date']);
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};