<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Apenas adicionar colunas que NÃO existem ainda na tabela original
            $table->uuid('procedure_id')->nullable()->after('contract_type_id');
            $table->uuid('ura_id')->nullable()->after('procedure_id');
            
            $table->string('procedure_type', 30)->nullable()->after('ura_id');
            $table->string('procedure_number', 50)->nullable()->after('procedure_type');
            
            $table->date('publication_date')->nullable()->after('bna_registration_date');
            $table->string('diary_series', 10)->nullable()->after('publication_date');
            $table->string('publication_number', 50)->nullable()->after('diary_series');
            
            // Status mais detalhado que o boolean original
            $table->string('tribunal_visto_status', 20)->nullable()->after('tribunal_de_contas_visto');
            
            // Receções (novas)
            $table->date('provisional_receipt_date')->nullable()->after('tribunal_visto_date');
            $table->date('definitive_receipt_date')->nullable()->after('provisional_receipt_date');
            
            // Foreign keys
            $table->foreign('procedure_id')->references('id')->on('contract_procedures')->nullOnDelete();
            $table->foreign('ura_id')->references('id')->on('uras')->nullOnDelete();
            
            // Índices
            $table->index('procedure_type');
            $table->index('tribunal_visto_status');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['procedure_id']);
            $table->dropForeign(['ura_id']);
            
            $table->dropIndex(['procedure_type']);
            $table->dropIndex(['tribunal_visto_status']);
            
            $table->dropColumn([
                'procedure_id',
                'ura_id',
                'procedure_type',
                'procedure_number',
                'publication_date',
                'diary_series',
                'publication_number',
                'tribunal_visto_status',
                'provisional_receipt_date',
                'definitive_receipt_date',
            ]);
        });
    }
};