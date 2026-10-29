<?php

namespace Tests\Feature\Modulos;

use App\Enums\EstadoGeneral;
use App\Enums\TipoMovimientoInventario;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Database\Seeders\FerreteriaProductosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgarProductosDemoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;

    private Sucursal $sucursal;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        $this->sucursal = Sucursal::factory()->create(['empresa_id' => $this->empresa->id, 'es_principal' => true]);
        $this->user = User::factory()->create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id]);
    }

    private function contarProductos(): int
    {
        return Producto::withoutGlobalScopes()->withTrashed()->where('empresa_id', $this->empresa->id)->count();
    }

    public function test_simulacion_no_borra_nada(): void
    {
        (new FerreteriaProductosSeeder)->run($this->empresa);
        $antes = $this->contarProductos();

        $this->artisan('app:purgar-productos-demo', ['empresa_id' => $this->empresa->id, '--detalle' => true])
            ->expectsOutputToContain('Productos propios de la empresa (no vienen del seeder, no se tocan): 0')
            ->expectsOutputToContain("Productos de prueba (del seeder) encontrados: {$antes}")
            ->expectsOutputToContain('Simulación')
            ->assertExitCode(0);

        $this->assertSame($antes, $this->contarProductos());
    }

    public function test_elimina_productos_demo_y_conserva_los_del_usuario_y_los_usados(): void
    {
        $categoriaPrevia = Categoria::withoutGlobalScopes()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Mi categoría',
            'activo' => true,
        ]);

        $propio = Producto::withoutGlobalScopes()->create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $categoriaPrevia->id,
            'nombre' => 'Producto real del cliente',
            'codigo' => 'REAL-001',
            'precio_venta' => 1000,
            'stock' => 3,
            'stock_minimo' => 1,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        (new FerreteriaProductosSeeder)->run($this->empresa);

        // El usuario ya movió un producto sembrado: debe conservarse
        $usado = Producto::withoutGlobalScopes()->where('empresa_id', $this->empresa->id)->where('codigo', 'FIJ-001')->first();
        MovimientoInventario::withoutGlobalScopes()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'producto_id' => $usado->id,
            'user_id' => $this->user->id,
            'tipo' => TipoMovimientoInventario::AJUSTE_NEGATIVO->value,
            'cantidad' => 1,
            'costo_unitario' => 0,
            'stock_anterior' => 60,
            'stock_posterior' => 59,
            'referencia' => 'Ajuste manual',
        ]);

        $this->artisan('app:purgar-productos-demo', ['empresa_id' => $this->empresa->id, '--ejecutar' => true])
            ->assertExitCode(0);

        $restantes = Producto::withoutGlobalScopes()->withTrashed()->where('empresa_id', $this->empresa->id)->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$propio->id, $usado->id], $restantes);

        $this->assertNotNull(Categoria::withoutGlobalScopes()->find($categoriaPrevia->id));
        $this->assertNotNull(Categoria::withoutGlobalScopes()->find($usado->categoria_id));
        $this->assertSame(1, Categoria::withoutGlobalScopes()->where('empresa_id', $this->empresa->id)
            ->where('nombre', '!=', 'Mi categoría')->count());
    }

    public function test_seed_company_data_se_bloquea_en_produccion(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('app:seed-company-data', ['empresa_id' => $this->empresa->id])
            ->assertExitCode(1);

        $this->assertSame(0, $this->contarProductos());
    }
}
