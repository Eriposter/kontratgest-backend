<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->uuid('contracting_procedure_id')
                  ->nullable()
                  ->after('pac_need_id')
                  ->constrained('contracting_procedures')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['contracting_procedure_id']);
            $table->dropColumn('contracting_procedure_id');
        });
    }
};