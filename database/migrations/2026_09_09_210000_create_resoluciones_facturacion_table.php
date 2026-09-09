<?php

use App\Enums\EstadoResolucionFacturacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resoluciones_facturacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->string('numero_resolucion', 60);
            $table->string('prefijo', 10);
            $table->unsignedBigInteger('rango_desde');
            $table->unsignedBigInteger('rango_hasta');
            $table->unsignedBigInteger('consecutivo_actual');
            $table->date('fecha_inicio');
            $table->date('fecha_vigencia');
            $table->string('clave_tecnica', 255)->nullable();
            $table->string('estado', 30)->default(EstadoResolucionFacturacion::ACTIVA->value);
            $table->boolean('es_predeterminada')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'prefijo', 'numero_resolucion'], 'resoluciones_empresa_prefijo_num_unique');
            $table->index(['empresa_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resoluciones_facturacion');
    }
};
