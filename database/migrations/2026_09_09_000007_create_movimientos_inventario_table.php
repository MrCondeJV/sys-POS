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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('sucursal_destino_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Tipo de movimiento (ENTRADA_COMPRA, SALIDA_VENTA, AJUSTE_POSITIVO, etc.)
            $table->string('tipo', 30);

            // Magnitud del movimiento (positivo) y costo histórico
            $table->decimal('cantidad', 14, 2);
            $table->decimal('costo_unitario', 14, 2)->default(0);

            // Trazabilidad inmutable: existencias antes y después en esta sucursal
            $table->decimal('stock_anterior', 14, 2);
            $table->decimal('stock_posterior', 14, 2);

            // Documento / justificación
            $table->string('referencia', 150);
            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices para consultas operacionales de Kardex
            $table->index(['empresa_id', 'sucursal_id', 'producto_id'], 'mov_inv_emp_suc_prod_idx');
            $table->index(['empresa_id', 'tipo'], 'mov_inv_emp_tipo_idx');
            $table->index(['empresa_id', 'created_at'], 'mov_inv_emp_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
