<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Asignación múltiple de sucursales por usuario
        Schema::create('sucursal_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'sucursal_id']);
            $table->index(['empresa_id', 'sucursal_id']);
        });

        // 2. Encabezado de traslados entre sucursales
        Schema::create('traslados_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_origen_id')->constrained('sucursales')->restrictOnDelete();
            $table->foreignId('sucursal_destino_id')->constrained('sucursales')->restrictOnDelete();
            $table->string('consecutivo', 50);
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // Despachador
            $table->foreignId('user_receptor_id')->nullable()->constrained('users')->nullOnDelete(); // Receptor
            $table->string('estado', 30)->default('EN_TRANSITO'); // PENDIENTE, EN_TRANSITO, RECIBIDO, RECHAZADO, CANCELADO
            $table->string('motivo', 255);
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->dateTime('fecha_recepcion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'sucursal_origen_id']);
            $table->index(['empresa_id', 'sucursal_destino_id']);
        });

        // 3. Detalle de artículos trasladados
        Schema::create('traslado_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_sucursal_id')->constrained('traslados_sucursal')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained('producto_lotes')->nullOnDelete();
            $table->decimal('cantidad_enviada', 12, 3);
            $table->decimal('cantidad_recibida', 12, 3)->default(0);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->index(['traslado_sucursal_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslado_detalles');
        Schema::dropIfExists('traslados_sucursal');
        Schema::dropIfExists('sucursal_user');
    }
};
