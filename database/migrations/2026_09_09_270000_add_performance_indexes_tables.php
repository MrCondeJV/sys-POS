<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->index(['empresa_id', 'created_at'], 'idx_ventas_empresa_created');
            $table->index(['empresa_id', 'tipo_pago'], 'idx_ventas_empresa_tipo_pago');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->index(['empresa_id', 'producto_id', 'created_at'], 'idx_mov_inv_empresa_prod_date');
        });

        Schema::table('producto_lotes', function (Blueprint $table) {
            $table->index(['empresa_id', 'fecha_vencimiento'], 'idx_lotes_empresa_vencimiento');
        });

        Schema::table('cuentas_por_cobrar', function (Blueprint $table) {
            $table->index(['empresa_id', 'fecha_vencimiento'], 'idx_cpc_empresa_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('cuentas_por_cobrar', function (Blueprint $table) {
            $table->dropIndex('idx_cpc_empresa_vencimiento');
        });

        Schema::table('producto_lotes', function (Blueprint $table) {
            $table->dropIndex('idx_lotes_empresa_vencimiento');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropIndex('idx_mov_inv_empresa_prod_date');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_empresa_created');
            $table->dropIndex('idx_ventas_empresa_tipo_pago');
        });
    }
};
