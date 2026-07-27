<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se as tabelas existem
        if (!Schema::hasTable('role_has_permissions') || !Schema::hasTable('model_has_permissions') || !Schema::hasTable('model_has_roles')) {
            return;
        }

        // Corrigir role_has_permissions
        Schema::table('role_has_permissions', function (Blueprint $table) {
            // Drop foreign keys primeiro
            $table->dropForeign(['role_id']);
            $table->dropForeign(['permission_id']);
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->uuid('role_id')->change();
            $table->uuid('permission_id')->change();
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();
            
            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->cascadeOnDelete();
        });

        // Corrigir model_has_permissions
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id')->change();
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->cascadeOnDelete();
        });

        // Corrigir model_has_roles
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->uuid('role_id')->change();
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Reverter para bigInteger (não recomendado, mas possível)
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['permission_id']);
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->change();
            $table->unsignedBigInteger('permission_id')->change();
        });
    }
};