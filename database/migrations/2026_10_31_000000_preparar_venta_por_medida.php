<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Venta por medida (m², metro, kg...): inventario con 4 decimales, unidades que permiten fracciones,
 * unidad M2 por empresa y presentación en el detalle de cotizaciones.
 */
return new class extends Migration
{
    /** Unidades que por naturaleza se venden fraccionadas. */
    private const CODIGOS_FRACCIONABLES = ['M2', 'MT', 'ML', 'M', 'KG', 'GR', 'LT', 'L', 'GL', 'CM'];

    public function up(): void
    {
        // 1) Precisión de 4 decimales en existencias y cantidades (una caja de 1,5625 m² no se puede redondear)
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('stock', 14, 4)->default(0)->change();
            $table->decimal('stock_minimo', 14, 4)->default(0)->change();
        });
        Schema::table('inventarios', function (Blueprint $table) {
            $table->decimal('stock', 14, 4)->default(0)->change();
            $table->decimal('stock_minimo', 14, 4)->default(0)->change();
        });
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 4)->change();
            $table->decimal('stock_anterior', 14, 4)->change();
            $table->decimal('stock_posterior', 14, 4)->change();
        });
        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 4)->change();
        });
        Schema::table('devolucion_detalles', function (Blueprint $table) {
            $table->decimal('cantidad', 12, 4)->change();
        });
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->decimal('cantidad_devuelta', 12, 4)->default(0)->change();
        });
        Schema::table('traslado_detalles', function (Blueprint $table) {
            $table->decimal('cantidad_enviada', 12, 4)->change();
            $table->decimal('cantidad_recibida', 12, 4)->default(0)->change();
        });

        // 2) Unidades que permiten vender fracciones
        Schema::table('unidades_medida', function (Blueprint $table) {
            $table->boolean('permite_decimales')->default(false)->after('codigo');
        });
        DB::table('unidades_medida')->whereIn(DB::raw('UPPER(codigo)'), self::CODIGOS_FRACCIONABLES)->update(['permite_decimales' => true]);

        // 3) Unidad M2 en cada empresa que no la tenga
        $ahora = now();
        foreach (DB::table('empresas')->pluck('id') as $empresaId) {
            $existe = DB::table('unidades_medida')->where('empresa_id', $empresaId)->whereRaw('UPPER(codigo) = ?', ['M2'])->exists();
            if (! $existe) {
                DB::table('unidades_medida')->insert([
                    'empresa_id' => $empresaId,
                    'nombre' => 'Metro cuadrado',
                    'codigo' => 'M2',
                    'permite_decimales' => true,
                    'activo' => true,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        }

        // 4) Las cotizaciones recuerdan en qué presentación se cotizó cada línea (caja o m²)
        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            $table->foreignId('presentacion_id')->nullable()->after('producto_id')->constrained('producto_presentaciones')->nullOnDelete();
            $table->decimal('factor_conversion', 12, 4)->default(1)->after('cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presentacion_id');
            $table->dropColumn('factor_conversion');
        });
        Schema::table('unidades_medida', function (Blueprint $table) {
            $table->dropColumn('permite_decimales');
        });
        Schema::table('traslado_detalles', function (Blueprint $table) {
            $table->decimal('cantidad_enviada', 12, 3)->change();
            $table->decimal('cantidad_recibida', 12, 3)->default(0)->change();
        });
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->decimal('cantidad_devuelta', 12, 2)->default(0)->change();
        });
        Schema::table('devolucion_detalles', function (Blueprint $table) {
            $table->decimal('cantidad', 12, 2)->change();
        });
        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 2)->change();
        });
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 2)->change();
            $table->decimal('stock_anterior', 14, 2)->change();
            $table->decimal('stock_posterior', 14, 2)->change();
        });
        Schema::table('inventarios', function (Blueprint $table) {
            $table->decimal('stock', 14, 2)->default(0)->change();
            $table->decimal('stock_minimo', 14, 2)->default(0)->change();
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('stock', 14, 2)->default(0)->change();
            $table->decimal('stock_minimo', 14, 2)->default(0)->change();
        });
    }
};
