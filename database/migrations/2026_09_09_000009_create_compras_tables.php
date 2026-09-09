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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('numero_factura', 100);
            $table->date('fecha_emision');

            // Valores monetarios
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('impuestos', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->string('tipo_pago', 20)->default('CONTADO');
            $table->string('estado', 20)->default('REGISTRADA');
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'sucursal_id'], 'compras_emp_suc_idx');
            $table->index(['empresa_id', 'proveedor_id'], 'compras_emp_prov_idx');
            $table->index(['empresa_id', 'fecha_emision'], 'compras_emp_fecha_idx');
            $table->index(['empresa_id', 'estado'], 'compras_emp_estado_idx');
        });

        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            $table->decimal('cantidad', 14, 2);
            $table->decimal('costo_unitario', 14, 2);
            $table->decimal('porcentaje_iva', 5, 2)->default(0);
            $table->decimal('valor_iva', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('total', 14, 2);

            $table->timestamps();

            $table->index(['compra_id', 'producto_id'], 'compra_det_comp_prod_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_detalles');
        Schema::dropIfExists('compras');
    }
};
