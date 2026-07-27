<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identificação
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('nif', 14)->unique();
            $table->string('logo_path')->nullable();
            
            // Tipo de empresa
            $table->string('company_type', 20); // private, public, mixed
            $table->string('sector', 50)->nullable(); // water, energy, construction, etc.
            $table->string('legal_nature', 50)->nullable(); // EP, EPE, SA, LDA, etc.
            
            // Contacto
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province', 30)->nullable();
            
            // Configurações
            $table->jsonb('settings')->default('{}');
            /*
             * Exemplo:
             * {
             *   "default_currency": "AOA",
             *   "fiscal_year_start": "01-01",
             *   "requires_tribunal_visto_above": 50000000,
             *   "approval_thresholds": {
             *     "director": 10000000,
             *     "council": 50000000,
             *     "minister": 100000000
             *   }
             * }
             */
            
            // Features ativas
            $table->jsonb('enabled_features')->default('[]');
            /*
             * Exemplo para empresa pública:
             * ["public_procedures", "tribunal_contas", "ura", "fiscalizacao", "publications"]
             * 
             * Exemplo para empresa privada:
             * ["internal_workflow", "budget_control", "client_contracts"]
             */
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};