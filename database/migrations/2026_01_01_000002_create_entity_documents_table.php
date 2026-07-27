<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entity_id');
            
            $table->string('document_type', 50); 
            // agt_certificate, inss_certificate, commercial_registration, 
            // id_document, power_of_attorney, other
            
            $table->string('file_name');
            $table->string('file_path');         // Caminho no storage (S3/MinIO)
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_current')->default(true);
            
            $table->uuid('uploaded_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('entity_id')
                  ->references('id')->on('entities')
                  ->cascadeOnDelete();
            
            $table->index(['entity_id', 'document_type', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_documents');
    }
};