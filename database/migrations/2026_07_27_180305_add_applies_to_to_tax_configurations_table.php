<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_configurations', 'applies_to')) {
                $table->jsonb('applies_to')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_configurations', function (Blueprint $table) {
            $table->dropColumn('applies_to');
        });
    }
};