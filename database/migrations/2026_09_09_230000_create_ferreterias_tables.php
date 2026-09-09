<?php

use App\Enums\EstadoGeneral;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Presentaciones / Factores de Conversión de Unidades por Producto
        Schema::create('producto_presentaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->nullOnDelete();
            $table->string('nombre', 100); // Ej: "Caja x 100", "Rollo 50m", "Bulto 50kg"
            $table->decimal('factor_conversion', 12, 4); // Ej: 100.0000, 50.0000
            $table->string('codigo_barras', 100)->nullable();
            $table->decimal('precio_venta', 14, 2);
            $table->boolean('es_predeterminada')->default(false);
            $table->string('estado', 30)->default(EstadoGeneral::ACTIVO->value);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'producto_id', 'nombre'], 'presentacion_producto_unique');
            $table->index(['empresa_id', 'producto_id']);
        });

        // 2. Agregar campos a venta_detalles para guardar la presentación usada y el factor
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->foreignId('presentacion_id')->nullable()->constrained('producto_presentaciones')->nullOnDelete();
            $table->decimal('factor_conversion', 12, 4)->default(1.0000)->after('cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->dropForeign(['presentacion_id']);
            $table->dropColumn(['presentacion_id', 'factor_conversion']);
        });

        Schema::dropIfExists('producto_presentaciones');
    }
};
