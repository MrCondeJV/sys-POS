<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('caja_sesion_id')->nullable()->constrained('cajas_sesiones')->nullOnDelete();
            $table->string('numero_devolucion', 50);
            $table->string('tipo_devolucion', 20)->default('PARCIAL'); // TOTAL, PARCIAL
            $table->string('tipo_reintegro', 30)->default('EFECTIVO'); // EFECTIVO, SALDO_FAVOR, AJUSTE_CARTERA
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('impuesto', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('motivo')->nullable();
            $table->string('estado', 20)->default('COMPLETADA'); // COMPLETADA, ANULADA
            $table->timestamps();

            $table->unique(['empresa_id', 'numero_devolucion']);
            $table->index(['empresa_id', 'venta_id']);
        });

        Schema::create('devolucion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('devolucion_id')->constrained('devoluciones')->cascadeOnDelete();
            $table->foreignId('venta_detalle_id')->constrained('venta_detalles')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('impuesto_porcentaje', 5, 2)->default(0);
            $table->decimal('impuesto_monto', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->boolean('reingresa_inventario')->default(true);
            $table->timestamps();

            $table->index(['empresa_id', 'producto_id']);
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->decimal('cantidad_devuelta', 12, 2)->default(0)->after('cantidad');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('total_devuelto', 15, 2)->default(0)->after('total');
            $table->boolean('tiene_devolucion')->default(false)->after('total_devuelto');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['total_devuelto', 'tiene_devolucion']);
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->dropColumn('cantidad_devuelta');
        });

        Schema::dropIfExists('devolucion_detalles');
        Schema::dropIfExists('devoluciones');
    }
};
