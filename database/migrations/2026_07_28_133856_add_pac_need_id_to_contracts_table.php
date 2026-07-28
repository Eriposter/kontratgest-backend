<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'pac_need_id')) {
                $table->uuid('pac_need_id')->nullable()->after('company_id');
                $table->foreign('pac_need_id')
                      ->references('id')
                      ->on('plan_needs')
                      ->nullOnDelete();
                $table->index('pac_need_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['pac_need_id']);
            $table->dropColumn('pac_need_id');
        });
    }
};