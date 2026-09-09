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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            // Stock actual y umbral mínimo en esta sucursal específica
            $table->decimal('stock', 14, 2)->default(0);
            $table->decimal('stock_minimo', 14, 2)->default(0);

            // Ubicación física opcional dentro de la sucursal (ej: Pasillo 3, Estante B)
            $table->string('ubicacion', 100)->nullable();

            $table->timestamps();

            // Restricción única: un producto solo tiene un registro de stock por sucursal
            $table->unique(['empresa_id', 'sucursal_id', 'producto_id'], 'inventario_emp_suc_prod_unique');
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index(['empresa_id', 'producto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
