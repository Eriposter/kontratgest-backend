<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guarantee_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guarantee_id');
            
            $table->string('document_type', 50); 
            // guarantee_certificate, bank_letter, insurance_policy, 
            // release_document, execution_document
            
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            
            $table->uuid('uploaded_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('guarantee_id')
                  ->references('id')->on('guarantees')
                  ->cascadeOnDelete();
            
            $table->index(['guarantee_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guarantee_documents');
    }
};