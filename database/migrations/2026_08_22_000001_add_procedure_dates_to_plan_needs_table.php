<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_needs', function (Blueprint $table) {
            // Datas do procedimento de contratação
            $table->date('procedure_start_date')->nullable()->after('contract_id');
            $table->date('procedure_end_date')->nullable()->after('procedure_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('plan_needs', function (Blueprint $table) {
            $table->dropColumn(['procedure_start_date', 'procedure_end_date']);
        });
    }
};
