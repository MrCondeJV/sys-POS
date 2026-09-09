<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->after('empresa_id')->constrained('sucursales')->nullOnDelete();
            $table->string('telefono', 50)->nullable()->after('email');
            $table->string('cargo', 100)->nullable()->after('telefono');
            $table->string('estado', 20)->default('ACTIVO')->after('cargo');
            $table->softDeletes()->after('updated_at');

            $table->index(['empresa_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['empresa_id', 'sucursal_id', 'telefono', 'cargo', 'estado', 'deleted_at']);
        });
    }
};
