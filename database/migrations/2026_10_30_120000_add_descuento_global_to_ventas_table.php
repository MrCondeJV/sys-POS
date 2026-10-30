<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descuento en valor fijo sobre el total de una venta de contado, con su motivo.
     * El monto ya está repartido en el descuento de cada línea; estas columnas lo documentan.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('descuento_global', 14, 2)->default(0)->after('descuento');
            $table->string('motivo_descuento', 255)->nullable()->after('descuento_global');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['descuento_global', 'motivo_descuento']);
        });
    }
};
