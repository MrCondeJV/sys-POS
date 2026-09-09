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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial', 150);
            $table->string('razon_social', 200)->nullable();
            $table->string('tipo_documento', 20)->default('NIT');
            $table->string('nit', 50)->unique();
            $table->string('dv', 2)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->string('moneda', 10)->default('COP');
            $table->string('simbolo_moneda', 5)->default('$');
            $table->string('logo_path', 255)->nullable();
            $table->json('configuraciones')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
