<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('measurement_id')->nullable()->after('contract_id');
            $table->foreign('measurement_id')
                  ->references('id')->on('measurements')
                  ->nullOnDelete();
            $table->index('measurement_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['measurement_id']);
            $table->dropColumn('measurement_id');
        });
    }
};