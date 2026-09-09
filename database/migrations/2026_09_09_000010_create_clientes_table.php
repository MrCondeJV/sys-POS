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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('tipo_persona', 20)->default('NATURAL');
            $table->string('tipo_documento', 20)->default('CC');
            $table->string('numero_documento', 50);

            $table->string('razon_social', 200); // Nombre completo o razón social
            $table->string('nombre_comercial', 200)->nullable();

            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();

            // Parámetros de crédito para Fase 8
            $table->decimal('cupo_credito', 14, 2)->default(0);
            $table->unsignedInteger('plazo_dias')->default(0);

            $table->string('estado', 20)->default('ACTIVO');
            $table->boolean('es_predeterminado')->default(false); // true para CONSUMIDOR FINAL

            $table->timestamps();
            $table->softDeletes();

            // Restricción única por empresa y documento
            $table->unique(['empresa_id', 'numero_documento']);

            // Índices de optimización de búsqueda
            $table->index(['empresa_id', 'razon_social']);
            $table->index(['empresa_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
