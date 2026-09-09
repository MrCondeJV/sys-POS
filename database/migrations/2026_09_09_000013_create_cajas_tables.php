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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();

            $table->string('nombre', 100);
            $table->string('codigo', 50);
            $table->string('estado', 20)->default('ACTIVA');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'sucursal_id']);
        });

        Schema::create('cajas_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_cierre_id')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();

            $table->decimal('monto_apertura', 14, 2)->default(0);
            $table->decimal('monto_cierre_esperado', 14, 2)->nullable();
            $table->decimal('monto_cierre_contado', 14, 2)->nullable();
            $table->decimal('diferencia', 14, 2)->default(0);

            $table->decimal('total_ingresos', 14, 2)->default(0);
            $table->decimal('total_egresos', 14, 2)->default(0);
            $table->decimal('total_ventas_efectivo', 14, 2)->default(0);
            $table->decimal('total_ventas_electronico', 14, 2)->default(0);

            $table->string('estado', 20)->default('ABIERTA'); // ABIERTA, CERRADA
            $table->string('observaciones_apertura', 255)->nullable();
            $table->string('observaciones_cierre', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index(['caja_id', 'estado']);
            $table->index(['user_id', 'estado']);
        });

        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('caja_sesion_id')->constrained('cajas_sesiones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('tipo', 20); // INGRESO, EGRESO
            $table->string('concepto', 255);
            $table->decimal('monto', 14, 2);
            $table->string('metodo_pago', 30)->default('EFECTIVO');
            $table->string('comprobante', 100)->nullable();

            $table->string('origen_type')->nullable();
            $table->unsignedBigInteger('origen_id')->nullable();

            $table->timestamps();

            $table->index(['caja_sesion_id', 'tipo']);
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index(['origen_type', 'origen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
        Schema::dropIfExists('cajas_sesiones');
        Schema::dropIfExists('cajas');
    }
};
