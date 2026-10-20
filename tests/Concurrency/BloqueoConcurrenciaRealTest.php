<?php

namespace Tests\Concurrency;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoMovimientoCaja;
use App\Models\Caja;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('concurrency')]
class BloqueoConcurrenciaRealTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    /**
     * Fase A5: Verifica el bloqueo pesimista (SELECT ... FOR UPDATE) sobre MySQL/PostgreSQL.
     * En SQLite local se omite explícitamente porque SQLite bloquea a nivel de archivo/tabla y no soporta FOR UPDATE real.
     */
    public function test_bloqueo_for_update_en_descuento_de_stock_y_egreso_de_caja(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $this->markTestSkipped(
                'Fase A5: SQLite no soporta bloqueo de fila real (SELECT ... FOR UPDATE). Esta suite se ejecuta en el job mysql-concurrency de CI con MySQL 8.0.'
            );
        }

        $this->seed(RolesAndPermissionsSeeder::class);
        $empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id, 'es_principal' => true]);

        $cajero = User::factory()->create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($empresa->id);
        $cajero->assignRole(RolSistema::CAJERO->value);

        CompanyContext::setCompany($empresa);

        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Producto Concurrencia',
            'codigo' => 'CONC-01',
            'precio_venta' => 50000,
            'stock' => 1,
            'stock_minimo' => 0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $caja = Caja::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Caja Concurrencia',
            'codigo' => 'CAJ-CONC',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $sesion = app(AbrirCajaAction::class)->execute($caja, $cajero, 50000);

        // Verificar que un egreso que agota el saldo deja la caja en 0 y un segundo egreso consecutivo falla
        app(RegistrarMovimientoCajaAction::class)->execute(
            $sesion,
            TipoMovimientoCaja::EGRESO,
            'Primer retiro total',
            50000
        );

        $this->assertEquals(0.0, $sesion->fresh()->calcularSaldoEsperadoEfectivo());

        $this->expectException(\Exception::class);
        app(RegistrarMovimientoCajaAction::class)->execute(
            $sesion->fresh(),
            TipoMovimientoCaja::EGRESO,
            'Segundo retiro concurrente sin fondos',
            10000
        );
    }
}
