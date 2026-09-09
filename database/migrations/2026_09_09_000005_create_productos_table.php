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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->nullOnDelete();

            $table->string('codigo', 50)->nullable();
            $table->string('codigo_barras', 100)->nullable();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();

            // Precios con alta precisión comercial
            $table->decimal('precio_compra', 14, 2)->default(0);
            $table->decimal('precio_venta', 14, 2);
            $table->decimal('precio_mayorista', 14, 2)->nullable();
            $table->decimal('precio_distribuidor', 14, 2)->nullable();

            // Stock e inventario base (Fase 4 preparatoria)
            $table->decimal('stock', 14, 2)->default(0);
            $table->decimal('stock_minimo', 14, 2)->default(0);

            // Tributario e imagen
            $table->decimal('iva', 5, 2)->default(0); // Ej: 19.00, 5.00, 0.00
            $table->string('imagen_path', 255)->nullable();
            $table->string('estado', 20)->default('ACTIVO');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'codigo_barras']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
