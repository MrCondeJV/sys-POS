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
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('codigo', 20);
            $table->string('nombre', 100);
            $table->string('tipo', 30)->default('IVA');
            $table->decimal('porcentaje', 5, 2)->default(0.00);
            $table->boolean('es_retencion')->default(false);
            $table->boolean('por_defecto')->default(false);
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'estado']);
        });

        // Relacionar opcionalmente productos con impuestos configurables
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('impuesto_id')->nullable()->after('iva')->constrained('impuestos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['impuesto_id']);
            $table->dropColumn('impuesto_id');
        });

        Schema::dropIfExists('impuestos');
    }
};
