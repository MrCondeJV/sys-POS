<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listas_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('es_predeterminada')->default(false);
            $table->string('tipo_ajuste', 30)->default('FIJO');
            $table->decimal('porcentaje_defecto', 5, 2)->default(0);
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'estado']);
        });

        Schema::create('lista_precio_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('lista_precio_id')->constrained('listas_precios')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('precio', 15, 2);
            $table->timestamps();

            $table->unique(['lista_precio_id', 'producto_id']);
            $table->index(['empresa_id', 'producto_id']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('lista_precio_id')->nullable()->after('empresa_id')->constrained('listas_precios')->nullOnDelete();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('lista_precio_id')->nullable()->after('cliente_id')->constrained('listas_precios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['lista_precio_id']);
            $table->dropColumn('lista_precio_id');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['lista_precio_id']);
            $table->dropColumn('lista_precio_id');
        });

        Schema::dropIfExists('lista_precio_detalles');
        Schema::dropIfExists('listas_precios');
    }
};
