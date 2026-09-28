<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_needs', function (Blueprint $table) {
            $table->date('proposed_start_date')->nullable()->after('planned_quarter');
            $table->date('proposed_end_date')->nullable()->after('proposed_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('plan_needs', function (Blueprint $table) {
            $table->dropColumn(['proposed_start_date', 'proposed_end_date']);
        });
    }
};