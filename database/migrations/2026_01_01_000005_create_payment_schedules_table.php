<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            
            $table->string('milestone_name'); // "Adjudicação", "Fase 1", "Receção Definitiva"
            $table->integer('sequence_order')->default(0);
            
            $table->date('due_date')->nullable();
            $table->decimal('percentage', 5, 2)->nullable(); // % do total
            $table->decimal('amount', 15, 2)->nullable(); // Valor fixo
            
            $table->boolean('is_conditional')->default(false);
            $table->string('condition_type', 30)->nullable(); // measurement_approved, delivery_received
            $table->text('condition_description')->nullable();
            
            $table->string('status', 20)->default('pending'); // pending, paid, overdue
            $table->date('paid_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('contract_id')
                  ->references('id')->on('contracts')
                  ->cascadeOnDelete();
            
            $table->index(['contract_id', 'sequence_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};