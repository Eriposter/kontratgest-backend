<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            
            // Identificação
            $table->string('measurement_number', 50)->unique(); // AM/2026/00123
            $table->integer('sequence_number'); // 1, 2, 3... (sequencial por contrato)
            
            // Período de medição
            $table->date('period_start');
            $table->date('period_end');
            
            // Valores
            $table->decimal('total_amount', 15, 2);
            $table->decimal('cumulative_amount', 15, 2); // Acumulado até este auto
            $table->decimal('retention_percentage', 5, 2)->default(0.00); // % retenção garantia
            $table->decimal('retention_amount', 15, 2)->default(0.00);
            
            // Estado
            $table->string('status', 20)->default('draft'); // draft, submitted, approved, rejected, paid
            $table->text('observations')->nullable();
            
            // Aprovação
            $table->uuid('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Pagamento
            $table->uuid('payment_id')->nullable(); // FK para payments (quando pago)
            $table->date('paid_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('contract_id')
                  ->references('id')->on('contracts')
                  ->cascadeOnDelete();
            
            // Índices
            $table->index(['contract_id', 'sequence_number']);
            $table->index('status');
            $table->index('measurement_number');
            $table->index(['status', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};