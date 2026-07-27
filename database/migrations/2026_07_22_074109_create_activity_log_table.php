<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            
            // Subject (polimórfico) - usar UUID explicitamente
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id']);
            
            $table->string('event')->nullable();
            
            // Causer (polimórfico) - usar UUID explicitamente
            $table->string('causer_type')->nullable();
            $table->uuid('causer_id')->nullable();
            $table->index(['causer_type', 'causer_id']);
            
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            
            $table->index('log_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};