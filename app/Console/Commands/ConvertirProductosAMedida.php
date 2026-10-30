<?php

namespace App\Console\Commands;

use App\Actions\Inventario\ConvertirProductoAMedidaAction;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Convierte productos que hoy se manejan por caja (u otra unidad de empaque) a una unidad base de medida
 * (m² por defecto), conservando la caja como presentación.
 *
 * Por cada producto: el stock pasa de cajas a m², el costo pasa a costo por m², la caja queda como
 * presentación con su precio actual y el precio por m² queda en $0 para que el negocio lo defina
 * (el POS no permite vender por m² mientras esté en $0). Deja el movimiento en el kardex.
 *
 * Por defecto solo simula. Ejemplo:
 *   php artisan app:convertir-productos-a-medida 2 --item=PA6060=1.44 --item=PGR5252=1.62
 *   php artisan app:convertir-productos-a-medida 2 --archivo=storage/app/pisos.csv --ejecutar
 */
class ConvertirProductosAMedida extends Command
{
    public const REFERENCIA = ConvertirProductoAMedidaAction::REFERENCIA;

    /** @var array<int, string> Códigos del archivo que aún no tienen m² por caja */
    private array $pendientes = [];

    protected $signature = 'app:convertir-productos-a-medida
                            {empresa_id : ID de la empresa dueña de los productos}
                            {--archivo= : CSV (; o ,) con columnas codigo;m2_por_caja[;producto;medida;m2_por_pieza;piezas_por_caja]. Sin m2_por_caja se calcula piezas × m2_por_pieza; vacío = pendiente}
                            {--item=* : Producto a convertir como CODIGO=FACTOR (se puede repetir)}
                            {--unidad=M2 : Código de la unidad base destino}
                            {--ejecutar : Aplica la conversión. Sin esta opción solo se muestra la simulación}';

    protected $description = 'Convierte productos vendidos por caja a una unidad base de medida (m²) conservando la caja como presentación.';

    public function handle(): int
    {
        $empresa = Empresa::withoutGlobalScopes()->find($this->argument('empresa_id'));
        if (! $empresa) {
            $this->error("No se encontró la empresa con ID {$this->argument('empresa_id')}.");

            return self::FAILURE;
        }

        $unidadDestino = UnidadMedida::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->whereRaw('UPPER(codigo) = ?', [strtoupper($this->option('unidad'))])
            ->first();
        if (! $unidadDestino) {
            $this->error("La empresa no tiene la unidad '{$this->option('unidad')}'. Ejecute las migraciones o créela en Catálogos.");

            return self::FAILURE;
        }

        $items = $this->leerItems();
        if ($items === null) {
            return self::FAILURE;
        }
        if ($items === [] && $this->pendientes !== []) {
            $this->info('Ningún producto del archivo tiene todavía sus m² por caja: no hay nada para convertir.');

            return self::SUCCESS;
        }
        if ($items === []) {
            $this->error('Indique los productos con --item=CODIGO=FACTOR o --archivo=ruta.csv');

            return self::FAILURE;
        }

        $this->info("Empresa: {$empresa->nombre_comercial} (ID {$empresa->id}) · Unidad destino: {$unidadDestino->codigo} - {$unidadDestino->nombre}");

        $plan = [];
        $filas = [];
        foreach ($items as $codigo => $factor) {
            [$estado, $detalle, $producto] = $this->evaluar($empresa->id, $codigo, $factor, $unidadDestino);
            $filas[] = [$codigo, $producto?->nombre ?? '-', $factor, $estado, $detalle];
            if ($estado === 'OK') {
                $plan[] = [$producto, $factor];
            }
        }

        $this->table(['Código', 'Producto', 'Factor', 'Estado', 'Detalle (antes → después)'], $filas);

        if ($plan === []) {
            $this->warn('No hay productos para convertir.');

            return self::SUCCESS;
        }

        if (! $this->option('ejecutar')) {
            $this->warn('Simulación: no se modificó nada. Revise la tabla y vuelva a ejecutar con --ejecutar para aplicar.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan) {
            foreach ($plan as [$producto, $factor]) {
                $this->convertir($producto, $factor);
            }
        });

        $this->info('Conversión aplicada a '.count($plan).' producto(s). Recuerde definir el precio por '.$unidadDestino->codigo.' de cada uno (hoy queda en $0).');

        return self::SUCCESS;
    }

    /**
     * @return array<string, float>|null null si hubo un error de lectura
     */
    private function leerItems(): ?array
    {
        $items = [];

        foreach ((array) $this->option('item') as $par) {
            if (! preg_match('/^\s*([^=]+?)\s*=\s*([\d.,]+)\s*$/', $par, $m)) {
                $this->error("Formato inválido en --item={$par}. Use CODIGO=FACTOR, ej. PA6060=1.44");

                return null;
            }
            $items[$m[1]] = $this->numero($m[2]);
        }

        if ($archivo = $this->option('archivo')) {
            $ruta = is_file($archivo) ? $archivo : base_path($archivo);
            if (! is_file($ruta)) {
                $this->error("No se encontró el archivo {$archivo}");

                return null;
            }
            foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $i => $linea) {
                $columnas = str_getcsv($linea, str_contains($linea, ';') ? ';' : ',');
                if ($i === 0 || count($columnas) < 2 || trim($columnas[0]) === '') {
                    continue; // encabezado o línea incompleta
                }
                // Columnas: codigo; m2_por_caja; producto; medida; m2_por_pieza; piezas_por_caja.
                // Si no se escribió m2_por_caja, se calcula como piezas_por_caja × m2_por_pieza.
                $factor = trim($columnas[1]);
                if ($factor === '' && trim($columnas[4] ?? '') !== '' && trim($columnas[5] ?? '') !== '') {
                    $factor = (string) round($this->numero($columnas[5]) * $this->numero($columnas[4]), 4);
                }
                // Sin el dato todavía: se deja para después sin detener el resto
                if ($factor === '') {
                    $this->pendientes[] = trim($columnas[0]);

                    continue;
                }
                $items[trim($columnas[0])] = $this->numero($factor);
            }
            if ($this->pendientes !== []) {
                $this->warn('Pendientes sin m² por caja (no se convierten todavía): '.implode(', ', $this->pendientes));
            }
        }

        return $items;
    }

    private function numero(string $valor): float
    {
        return (float) str_replace(',', '.', trim($valor));
    }

    /**
     * @return array{0: string, 1: string, 2: ?Producto}
     */
    private function evaluar(int $empresaId, string $codigo, float $factor, UnidadMedida $unidadDestino): array
    {
        $producto = Producto::withoutGlobalScopes()->with('unidadMedida')
            ->where('empresa_id', $empresaId)->where('codigo', $codigo)->first();

        if (! $producto) {
            return ['NO ENCONTRADO', 'No existe un producto activo con ese código en la empresa', null];
        }
        if ($factor <= 0) {
            return ['ERROR', 'El factor debe ser mayor a cero', $producto];
        }
        if ((int) $producto->unidad_medida_id === (int) $unidadDestino->id) {
            return ['OMITIDO', "Ya está en {$unidadDestino->codigo}", $producto];
        }
        if (DB::table('lista_precio_detalles')->where('producto_id', $producto->id)->exists()) {
            return ['BLOQUEADO', 'Tiene precios especiales en listas de precios por caja: quítelos antes de convertir', $producto];
        }
        if (DB::table('traslado_detalles')->join('traslados_sucursal', 'traslados_sucursal.id', '=', 'traslado_detalles.traslado_sucursal_id')
            ->where('traslado_detalles.producto_id', $producto->id)->where('traslados_sucursal.estado', 'EN_TRANSITO')->exists()) {
            return ['BLOQUEADO', 'Tiene traslados en tránsito: recíbalos antes de convertir', $producto];
        }

        $stock = (float) Inventario::withoutGlobalScopes()->where('producto_id', $producto->id)->sum('stock');
        $unidadOrigen = $producto->unidadMedida?->codigo ?? 'sin unidad';
        $fmt = fn (float $n) => rtrim(rtrim(number_format($n, 4, ',', '.'), '0'), ',');

        return ['OK', sprintf(
            'Stock %s %s → %s %s · Caja $%s (se conserva) · Costo $%s → $%s/%s · Precio %s: $0 (por definir)',
            $fmt($stock), $unidadOrigen, $fmt($stock * $factor), $unidadDestino->codigo,
            number_format((float) $producto->precio_venta, 0, ',', '.'),
            number_format((float) $producto->precio_compra, 0, ',', '.'),
            number_format(round((float) $producto->precio_compra / $factor, 2), 0, ',', '.'), $unidadDestino->codigo,
            $unidadDestino->codigo
        ), $producto];
    }

    private function convertir(Producto $producto, float $factor): void
    {
        app(ConvertirProductoAMedidaAction::class)->execute($producto, $factor);
    }
}
