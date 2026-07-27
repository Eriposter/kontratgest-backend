<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            
            $table->string('document_type', 50); 
            // contract_draft, signed_contract, annex, technical_spec, 
            // measurement, delivery_note, amendment, termination
            
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            
            $table->integer('version')->default(1);
            $table->boolean('is_current')->default(true);
            
            $table->uuid('uploaded_by')->nullable();
            
            $table->timestamps();
            
            $table->foreign('contract_id')
                  ->references('id')->on('contracts')
                  ->cascadeOnDelete();
            
            $table->index(['contract_id', 'document_type', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_documents');
    }
};