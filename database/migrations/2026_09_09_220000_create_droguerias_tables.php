<?php

use App\Enums\EstadoGeneral;
use App\Enums\EstadoLote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Laboratorios Farmacéuticos
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('codigo', 50)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('estado', 30)->default(EstadoGeneral::ACTIVO->value);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'nombre']);
        });

        // 2. Principios Activos (Fármaco base)
        Schema::create('principios_activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('concentracion', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado', 30)->default(EstadoGeneral::ACTIVO->value);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'nombre', 'concentracion']);
        });

        // 3. Campos Farmacéuticos en Productos
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios')->nullOnDelete();
            $table->foreignId('principio_activo_id')->nullable()->constrained('principios_activos')->nullOnDelete();
            $table->string('registro_sanitario', 100)->nullable()->after('descripcion');
            $table->boolean('requiere_receta')->default(false)->after('registro_sanitario');
            $table->boolean('maneja_lotes')->default(false)->after('requiere_receta');
        });

        // 4. Lotes de Productos con Vencimiento y Trazabilidad
        Schema::create('producto_lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('numero_lote', 60);
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento');
            $table->decimal('stock_inicial', 12, 4)->default(0);
            $table->decimal('stock_actual', 12, 4)->default(0);
            $table->decimal('costo_unitario', 12, 2)->default(0);
            $table->string('estado', 30)->default(EstadoLote::DISPONIBLE->value);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'sucursal_id', 'producto_id', 'numero_lote'], 'lotes_unicos_sucursal');
            $table->index(['empresa_id', 'fecha_vencimiento', 'estado']);
        });

        // 5. Vincular lote a detalles de ventas y compras
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->constrained('producto_lotes')->nullOnDelete();
        });

        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->constrained('producto_lotes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn('lote_id');
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn('lote_id');
        });

        Schema::dropIfExists('producto_lotes');

        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['laboratorio_id']);
            $table->dropForeign(['principio_activo_id']);
            $table->dropColumn(['laboratorio_id', 'principio_activo_id', 'registro_sanitario', 'requiere_receta', 'maneja_lotes']);
        });

        Schema::dropIfExists('principios_activos');
        Schema::dropIfExists('laboratorios');
    }
};
