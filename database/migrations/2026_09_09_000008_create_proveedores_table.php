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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('razon_social', 200);
            $table->string('nombre_contacto', 150)->nullable();
            $table->string('tipo_documento', 20)->default('NIT');
            $table->string('numero_documento', 50);

            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();

            $table->string('estado', 20)->default('ACTIVO');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'numero_documento'], 'proveedores_emp_doc_unique');
            $table->index(['empresa_id', 'razon_social']);
            $table->index(['empresa_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
