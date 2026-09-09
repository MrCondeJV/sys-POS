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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('caja_sesion_id')->nullable()->constrained('cajas_sesiones')->nullOnDelete();

            $table->string('numero_venta', 50);
            $table->string('tipo_comprobante', 30)->default('TICKET');
            $table->dateTime('fecha');

            $table->string('tipo_pago', 20)->default('CONTADO'); // CONTADO, CREDITO
            $table->string('metodo_pago', 30)->default('EFECTIVO'); // EFECTIVO, TARJETA, TRANSFERENCIA, MIXTO, etc.

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('impuesto', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->decimal('pago_con', 14, 2)->nullable();
            $table->decimal('cambio', 14, 2)->default(0);

            $table->string('estado', 20)->default('COMPLETADA'); // COMPLETADA, ANULADA
            $table->string('observaciones', 500)->nullable();

            $table->foreignId('anulado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_anulacion')->nullable();
            $table->string('motivo_anulacion', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'numero_venta']);
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index(['empresa_id', 'fecha']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'cliente_id']);
        });

        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_unitario', 14, 2);
            $table->decimal('costo_unitario', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('impuesto_porcentaje', 5, 2)->default(0);
            $table->decimal('impuesto_monto', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('total', 14, 2);

            $table->timestamps();

            $table->index(['venta_id', 'producto_id']);
            $table->index(['empresa_id', 'producto_id']);
        });

        Schema::create('venta_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();

            $table->string('metodo_pago', 30);
            $table->decimal('monto', 14, 2);
            $table->string('referencia', 100)->nullable();

            $table->timestamps();

            $table->index(['venta_id', 'metodo_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_pagos');
        Schema::dropIfExists('venta_detalles');
        Schema::dropIfExists('ventas');
    }
};
