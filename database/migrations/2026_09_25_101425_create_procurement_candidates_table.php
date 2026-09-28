<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procedure_id')->constrained('procurement_procedures')->cascadeOnDelete();
            $table->uuid('entity_id')->constrained('entities')->cascadeOnDelete();
            
            $table->enum('status', ['qualified', 'disqualified', 'winner', 'runner_up'])->default('qualified');
            $table->decimal('proposed_amount', 15, 2)->nullable();
            $table->integer('technical_score')->nullable();
            $table->integer('financial_score')->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_candidates');
    }
};