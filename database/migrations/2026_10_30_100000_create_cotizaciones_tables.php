<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cotizaciones comerciales: no afectan inventario, caja ni cartera hasta convertirse en venta.
     */
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('lista_precio_id')->nullable()->constrained('listas_precios')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('numero_cotizacion', 50);
            $table->date('fecha');
            $table->date('fecha_validez')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('impuesto', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->string('estado', 20)->default('PENDIENTE'); // PENDIENTE, CONVERTIDA, ANULADA
            $table->string('observaciones', 500)->nullable();

            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->dateTime('fecha_conversion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'numero_cotizacion']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'cliente_id']);
            $table->index(['empresa_id', 'fecha']);
        });

        Schema::create('cotizacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();

            // Nombre del producto al momento de cotizar, para que el documento no cambie si se renombra
            $table->string('descripcion', 255);
            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio_unitario', 14, 2);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('impuesto_porcentaje', 5, 2)->default(0);
            $table->decimal('impuesto_monto', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('total', 14, 2);

            $table->timestamps();

            $table->index(['cotizacion_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_detalles');
        Schema::dropIfExists('cotizaciones');
    }
};
