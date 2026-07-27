<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_progress_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            
            $table->decimal('progress_percentage', 5, 2); // 0.00 a 100.00
            $table->string('update_type', 20); // manual, automatic, payment, measurement
            $table->text('notes')->nullable();
            $table->jsonb('evidence')->default('[]'); // URLs de fotos, documentos, etc.
            
            $table->uuid('updated_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('contract_id')
                  ->references('id')->on('contracts')
                  ->cascadeOnDelete();
            
            $table->index(['contract_id', 'created_at']);
        });

        // Adicionar campos à tabela contracts
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('current_progress', 5, 2)->default(0.00)->after('status');
            $table->timestamp('progress_last_updated_at')->nullable()->after('current_progress');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['current_progress', 'progress_last_updated_at']);
        });
        
        Schema::dropIfExists('contract_progress_updates');
    }
};