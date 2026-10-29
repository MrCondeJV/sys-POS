<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurgarProductosDemo extends Command
{
    /**
     * Referencia con la que FerreteriaProductosSeeder asienta el kardex inicial.
     * Es la marca que permite distinguir los productos sembrados de los creados por usuarios.
     */
    public const REFERENCIA_SEMILLA = 'Apertura de Inventario Inicial Ferretería Demo';

    /**
     * Tablas de negocio que, si referencian un producto, indican que el usuario ya lo usa.
     * Varias tienen cascadeOnDelete, por lo que borrar el producto destruiría esos registros.
     */
    private const TABLAS_DE_USO = [
        'venta_detalles',
        'compra_detalles',
        'devolucion_detalles',
        'traslado_detalles',
        'lista_precio_detalles',
        'producto_lotes',
        'producto_presentaciones',
    ];

    /**
     * Margen alrededor de la ejecución del seeder para reconocer catálogos creados por él.
     */
    private const MINUTOS_VENTANA = 10;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purgar-productos-demo
                            {empresa_id : ID de la empresa a limpiar}
                            {--ejecutar : Aplica el borrado. Sin esta opción solo se muestra el reporte}
                            {--detalle : Lista cada producto de cada grupo en el reporte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina los productos de prueba de FerreteriaProductosSeeder que ningún usuario ha utilizado.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $empresa = Empresa::withoutGlobalScopes()->find($this->argument('empresa_id'));

        if (! $empresa) {
            $this->error("No se encontró ninguna empresa con el ID: {$this->argument('empresa_id')}");

            return Command::FAILURE;
        }

        $this->info("Empresa: {$empresa->nombre_comercial} (ID: {$empresa->id})");

        $semillas = DB::table('movimientos_inventario')
            ->where('empresa_id', $empresa->id)
            ->where('referencia', self::REFERENCIA_SEMILLA)
            ->get(['producto_id', 'created_at']);

        $productoIds = $semillas->pluck('producto_id')->unique()->values();

        // Productos creados por los usuarios: nunca se tocan, solo se reportan
        $propios = DB::table('productos')
            ->where('empresa_id', $empresa->id)
            ->whereNotIn('id', $productoIds)
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'deleted_at']);

        $this->line("Productos propios de la empresa (no vienen del seeder, no se tocan): {$propios->count()}");

        if ($this->option('detalle') && $propios->isNotEmpty()) {
            $this->table(
                ['ID', 'Código', 'Nombre', 'Eliminado (papelera)'],
                $propios->map(fn ($p) => [$p->id, $p->codigo, $p->nombre, $p->deleted_at ? 'Sí' : 'No'])->all()
            );
        }

        if ($productoIds->isEmpty()) {
            $this->info('No hay productos de prueba en esta empresa.');

            return Command::SUCCESS;
        }

        $productos = DB::table('productos')
            ->where('empresa_id', $empresa->id)
            ->whereIn('id', $productoIds)
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'categoria_id', 'marca_id', 'unidad_medida_id']);

        $usados = $this->productosUsados($empresa->id, $productoIds);
        [$conservar, $eliminar] = $productos->partition(fn ($p) => $usados->has($p->id));

        $this->line("Productos de prueba (del seeder) encontrados: {$productos->count()}");
        $this->line("  - A eliminar (sin uso): {$eliminar->count()}");
        $this->line("  - A conservar (ya usados por la empresa): {$conservar->count()}");

        if ($this->option('detalle') && $eliminar->isNotEmpty()) {
            $this->table(
                ['ID', 'Código', 'Nombre (se eliminaría)'],
                $eliminar->map(fn ($p) => [$p->id, $p->codigo, $p->nombre])->all()
            );
        }

        if ($conservar->isNotEmpty()) {
            $this->table(
                ['ID', 'Código', 'Nombre', 'Motivo'],
                $conservar->map(fn ($p) => [$p->id, $p->codigo, $p->nombre, implode(', ', $usados[$p->id])])->all()
            );
        }

        $desde = $semillas->min('created_at');
        $hasta = $semillas->max('created_at');
        $catalogos = $this->catalogosHuerfanos($empresa->id, $eliminar, $desde, $hasta);

        foreach ($catalogos as $tabla => $ids) {
            $this->line("  - {$tabla} sin uso creados por el seeder: ".count($ids));
        }

        if (! $this->option('ejecutar')) {
            $this->warn('Simulación: no se borró nada. Vuelva a ejecutar con --ejecutar para aplicar.');

            return Command::SUCCESS;
        }

        DB::transaction(function () use ($empresa, $eliminar, $catalogos) {
            $ids = $eliminar->pluck('id');

            // inventarios y movimientos_inventario caen por cascadeOnDelete
            DB::table('productos')->where('empresa_id', $empresa->id)->whereIn('id', $ids)->delete();

            foreach ($catalogos as $tabla => $catIds) {
                DB::table($tabla)->where('empresa_id', $empresa->id)->whereIn('id', $catIds)->delete();
            }
        });

        $this->info("Se eliminaron {$eliminar->count()} productos de prueba.");

        return Command::SUCCESS;
    }

    /**
     * Devuelve [producto_id => [motivos]] para los productos con actividad real.
     */
    private function productosUsados(int $empresaId, Collection $productoIds): Collection
    {
        $usados = collect();

        foreach (self::TABLAS_DE_USO as $tabla) {
            DB::table($tabla)->whereIn('producto_id', $productoIds)->distinct()->pluck('producto_id')
                ->each(function ($id) use ($usados, $tabla) {
                    $usados[$id] = [...($usados[$id] ?? []), $tabla];
                });
        }

        // Cualquier movimiento de kardex distinto a la carga inicial (ventas, ajustes, traslados...)
        DB::table('movimientos_inventario')
            ->where('empresa_id', $empresaId)
            ->whereIn('producto_id', $productoIds)
            ->where(fn ($q) => $q->whereNull('referencia')->orWhere('referencia', '!=', self::REFERENCIA_SEMILLA))
            ->distinct()
            ->pluck('producto_id')
            ->each(function ($id) use ($usados) {
                $usados[$id] = [...($usados[$id] ?? []), 'movimientos_inventario'];
            });

        return $usados;
    }

    /**
     * Categorías, marcas y unidades usadas solo por los productos a eliminar y creadas
     * durante la ejecución del seeder. Las que el usuario ya tenía o sigue usando se conservan.
     *
     * @return array<string, array<int>>
     */
    private function catalogosHuerfanos(int $empresaId, Collection $eliminar, string $desde, string $hasta): array
    {
        $desde = now()->parse($desde)->subMinutes(self::MINUTOS_VENTANA);
        $hasta = now()->parse($hasta)->addMinutes(self::MINUTOS_VENTANA);
        $idsEliminar = $eliminar->pluck('id');

        $referencias = [
            'categorias' => ['categoria_id' => ['productos']],
            'marcas' => ['marca_id' => ['productos']],
            'unidades_medida' => ['unidad_medida_id' => ['productos', 'producto_presentaciones']],
        ];

        $resultado = [];

        foreach ($referencias as $tabla => $columnas) {
            foreach ($columnas as $columna => $tablasQueReferencian) {
                $candidatos = $eliminar->pluck($columna)->filter()->unique();

                $ids = DB::table($tabla)
                    ->where('empresa_id', $empresaId)
                    ->whereIn('id', $candidatos)
                    ->whereBetween('created_at', [$desde, $hasta])
                    ->pluck('id')
                    ->reject(function ($id) use ($tablasQueReferencian, $columna, $idsEliminar) {
                        foreach ($tablasQueReferencian as $ref) {
                            $query = DB::table($ref)->where($columna, $id);
                            if ($ref === 'productos') {
                                $query->whereNotIn('id', $idsEliminar);
                            }
                            if ($query->exists()) {
                                return true;
                            }
                        }

                        return false;
                    })
                    ->values()
                    ->all();

                $resultado[$tabla] = $ids;
            }
        }

        return $resultado;
    }
}
