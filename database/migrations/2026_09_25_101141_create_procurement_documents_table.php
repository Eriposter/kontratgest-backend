<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procedure_id')->constrained('procurement_procedures')->cascadeOnDelete();
            $table->uuid('phase_id')->nullable()->constrained('procurement_phases')->nullOnDelete();
            
            $table->string('document_type');
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            
            $table->uuid('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_documents');
    }
};