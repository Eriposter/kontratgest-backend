<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contract_types')
            ->where('code', 'other')
            ->update(['requires_specification' => true]);
    }

    public function down(): void
    {
        DB::table('contract_types')
            ->where('code', 'other')
            ->update(['requires_specification' => false]);
    }
};