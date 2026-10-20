<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\ListaPrecio;
use App\Support\Tenancy\CompanyContext;
use App\Support\Tenancy\BranchContext;
use App\Enums\RolSistema;
use App\Enums\EstadoGeneral;
use App\Enums\TipoAjusteListaPrecio;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisualTourTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Empresa $empresa;
    protected Sucursal $sucursal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresa = Empresa::factory()->create();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresa);

        $this->sucursal = Sucursal::factory()->create(['empresa_id' => $this->empresa->id]);

        $this->user = User::factory()->create(['empresa_id' => $this->empresa->id]);
        setPermissionsTeamId($this->empresa->id);
        $this->user->assignRole(RolSistema::ADMIN_EMPRESA->value);

        CompanyContext::setCompany($this->empresa);
        BranchContext::setId($this->sucursal->id);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        BranchContext::clear();
        parent::tearDown();
    }

    public function test_recorrido_visual_todos_los_modulos_operativos(): void
    {
        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Producto de Prueba',
            'codigo' => 'PROD-TEST-01',
            'precio_venta' => 10000,
            'costo' => 5000,
            'stock' => 20,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);
        $cuenta = CuentaPorCobrar::factory()->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'sucursal_id' => $this->sucursal->id,
        ]);
        $lista = ListaPrecio::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Público General',
            'codigo' => 'PUB-01',
            'tipo_ajuste' => TipoAjusteListaPrecio::FIJO,
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $routes = [
            'dashboard',
            'pos.index',
            'ventas.index',
            'clientes.index',
            'clientes.create',
            'cartera.index',
            'cartera.create',
            'productos.index',
            'productos.create',
            'inventario.index',
            'inventario.ajuste.create',
            'inventario.traslado.create',
            'traslados.index',
            'traslados.create',
            'listas-precios.index',
            'listas-precios.create',
            'compras.index',
            'compras.create',
            'proveedores.index',
            'devoluciones.index',
            'cajas.index',
            'documentos.index',
            'resoluciones.index',
            'reportes.index',
            'reportes.ventas',
            'reportes.cajas',
            'reportes.cartera',
            'reportes.compras',
            'reportes.impuestos',
            'reportes.inventario',
            'reportes.metodos-pago',
            'reportes.utilidad',
            'reportes.multisucursal',
            'auditoria.index',
            'empresas.index',
            'sucursales.index',
            'usuarios.index',
            'usuarios.create',
            'impuestos.index',
            'catalogos.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->get(route($route));
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Ruta {$route} fallo con status {$response->status()}"
            );
        }

        if ($producto) {
            $response = $this->actingAs($this->user)->get(route('productos.edit', $producto));
            $this->assertEquals(200, $response->status());

            $response = $this->actingAs($this->user)->get(route('inventario.kardex', $producto));
            $this->assertEquals(200, $response->status());
        }

        if ($cliente) {
            $response = $this->actingAs($this->user)->get(route('clientes.edit', $cliente));
            $this->assertEquals(200, $response->status());
        }

        if ($cuenta) {
            $response = $this->actingAs($this->user)->get(route('cartera.show', $cuenta));
            $this->assertEquals(200, $response->status());
        }

        if ($lista) {
            $response = $this->actingAs($this->user)->get(route('listas-precios.edit', $lista));
            $this->assertEquals(200, $response->status());
        }
    }
}
