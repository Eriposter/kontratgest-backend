<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identificação
            $table->string('entity_type', 30)->index(); // EntityType enum
            $table->string('name');                      // Nome comercial / fantasia
            $table->string('legal_name')->nullable();    // Razão social (como na certidão)
            $table->string('nif', 14)->unique()->nullable(); // NIF angolano
            $table->string('nif_type', 20)->default('collective'); // individual | collective
            
            // Contacto
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_alt')->nullable();
            $table->string('website')->nullable();
            
            // Endereço
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province', 30)->nullable(); // Province enum
            $table->string('postal_code', 20)->nullable();
            
            // Dados bancários (JSONB - suporte a múltiplas contas)
            $table->jsonb('bank_accounts')->default('[]');
            /*
             * Exemplo:
             * [
             *   {
             *     "bank": "BAI",
             *     "iban": "AO06004000001234567890123",
             *     "account_holder": "Empresa X, Lda",
             *     "is_default": true
             *   }
             * ]
             */
            
            // Compliance & Certidões (crítico para Angola)
            $table->date('agt_certificate_expiry')->nullable();   // Certidão AGT
            $table->date('inss_certificate_expiry')->nullable();  // Certidão INSS
            $table->boolean('is_tax_exempt')->default(false);
            $table->string('tax_regime', 30)->default('general'); // general | simplified | exempt
            
            // Classificação
            $table->string('activity_code', 20)->nullable();      // CAE / Código de atividade
            $table->text('notes')->nullable();
            
            // Estado
            $table->string('status', 20)->default('active'); // active, suspended, blacklisted
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices de pesquisa
            $table->index(['entity_type', 'status']);
            $table->index('province');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};