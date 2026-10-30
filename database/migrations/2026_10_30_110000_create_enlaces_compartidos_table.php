<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enlaces cortos y temporales para compartir el PDF de ventas y cotizaciones (WhatsApp Web).
     */
    public function up(): void
    {
        Schema::create('enlaces_compartidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('token', 32)->unique();
            $table->string('tipo', 20); // venta, cotizacion
            $table->unsignedBigInteger('documento_id');
            $table->dateTime('expira_en');
            $table->unsignedInteger('visitas')->default(0);
            $table->timestamps();

            $table->index(['tipo', 'documento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enlaces_compartidos');
    }
};
