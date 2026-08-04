<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_types', function (Blueprint $table) {
            if (!Schema::hasColumn('contract_types', 'requires_specification')) {
                $table->boolean('requires_specification')->default(false)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contract_types', function (Blueprint $table) {
            $table->dropColumn('requires_specification');
        });
    }
};