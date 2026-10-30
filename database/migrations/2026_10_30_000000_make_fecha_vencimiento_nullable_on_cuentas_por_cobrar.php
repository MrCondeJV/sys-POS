<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El crédito ya no maneja plazos de pago: la fecha de vencimiento queda opcional
     * y se conservan las fechas históricas ya registradas.
     */
    public function up(): void
    {
        Schema::table('cuentas_por_cobrar', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cuentas_por_cobrar', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable(false)->change();
        });
    }
};
