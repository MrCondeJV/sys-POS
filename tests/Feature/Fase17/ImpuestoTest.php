<?php

namespace Tests\Feature\Fase17;

use App\Enums\EstadoGeneral;
use App\Enums\PermisoSistema;
use App\Enums\RolSistema;
use App\Enums\TipoImpuesto;
use App\Models\Empresa;
use App\Models\Impuesto;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpuestoTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $vendedorA;
    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Alfa Impuestos',
            'nit' => '901111333-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sucursal Central A',
            'codigo' => 'SUC-A1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'Admin Alfa',
            'email' => 'admin@alfa.test',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->vendedorA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'Vendedor Alfa',
            'email' => 'vendedor@alfa.test',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->vendedorA->assignRole(RolSistema::VENDEDOR->value);

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Beta Impuestos',
            'nit' => '902222333-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sucursal Central B',
            'codigo' => 'SUC-B1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'name' => 'Admin Beta',
            'email' => 'admin@beta.test',
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $response = $this->get(route('impuestos.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $response = $this->actingAs($this->vendedorA)->get(route('impuestos.index'));
        $response->assertForbidden();
    }

    public function test_admin_puede_listar_impuestos(): void
    {
        CompanyContext::setCompanyId($this->empresaA->id);

        Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'IVA 19%',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA,
            'por_defecto' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('impuestos.index'));
        $response->assertOk();
        $response->assertSee('IVA 19%');
        $response->assertSee('IVA19');
        $response->assertSee('19%');
    }

    public function test_admin_puede_crear_impuesto(): void
    {
        $data = [
            'nombre' => 'IVA General 19%',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA->value,
            'descripcion' => 'Tarifa general IVA Colombia',
            'por_defecto' => '1',
            'estado' => EstadoGeneral::ACTIVO->value,
        ];

        $response = $this->actingAs($this->adminA)->post(route('impuestos.store'), $data);

        $response->assertRedirect(route('impuestos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('impuestos', [
            'empresa_id' => $this->empresaA->id,
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'por_defecto' => 1,
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);
    }

    public function test_codigo_debe_ser_unico_por_empresa(): void
    {
        CompanyContext::setCompanyId($this->empresaA->id);

        Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'IVA 19%',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA,
            'por_defecto' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Intentar registrar el mismo código en Empresa A debe fallar
        $response = $this->actingAs($this->adminA)->post(route('impuestos.store'), [
            'nombre' => 'Otro IVA',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA->value,
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);

        $response->assertSessionHasErrors('codigo');

        // Pero en Empresa B sí se permite el mismo código
        $responseB = $this->actingAs($this->adminB)->post(route('impuestos.store'), [
            'nombre' => 'IVA Empresa B',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA->value,
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);

        $responseB->assertRedirect(route('impuestos.index'));
        $this->assertDatabaseHas('impuestos', [
            'empresa_id' => $this->empresaB->id,
            'codigo' => 'IVA19',
        ]);
    }

    public function test_admin_puede_actualizar_impuesto(): void
    {
        CompanyContext::setCompanyId($this->empresaA->id);

        $impuesto = Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'IVA Reducido 5%',
            'codigo' => 'IVA5',
            'porcentaje' => 5.00,
            'tipo' => TipoImpuesto::IVA,
            'por_defecto' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('impuestos.update', $impuesto), [
            'nombre' => 'IVA Reducido 5% Modificado',
            'codigo' => 'IVA5',
            'porcentaje' => 5.00,
            'tipo' => TipoImpuesto::IVA->value,
            'descripcion' => 'Actualizado correctamente',
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);

        $response->assertRedirect(route('impuestos.index'));
        $this->assertDatabaseHas('impuestos', [
            'id' => $impuesto->id,
            'nombre' => 'IVA Reducido 5% Modificado',
        ]);
    }

    public function test_marcar_por_defecto_desmarca_otros_en_la_misma_empresa(): void
    {
        CompanyContext::setCompanyId($this->empresaA->id);

        $impuesto1 = Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'IVA 19%',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA,
            'por_defecto' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $impuesto2 = Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Exento 0%',
            'codigo' => 'EXENTO',
            'porcentaje' => 0.00,
            'tipo' => TipoImpuesto::EXENTO,
            'por_defecto' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('impuestos.por-defecto', $impuesto2));
        $response->assertRedirect(route('impuestos.index'));

        $this->assertDatabaseHas('impuestos', [
            'id' => $impuesto1->id,
            'por_defecto' => false,
        ]);
        $this->assertDatabaseHas('impuestos', [
            'id' => $impuesto2->id,
            'por_defecto' => true,
        ]);
    }

    public function test_no_se_puede_eliminar_impuesto_vinculado_a_productos(): void
    {
        CompanyContext::setCompanyId($this->empresaA->id);

        $impuesto = Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'IVA 19%',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA,
            'por_defecto' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Producto::create([
            'empresa_id' => $this->empresaA->id,
            'impuesto_id' => $impuesto->id,
            'codigo' => 'PROD-TAX-1',
            'nombre' => 'Producto con Impuesto',
            'precio_compra' => 5000,
            'precio_venta' => 10000,
            'stock' => 10,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('impuestos.destroy', $impuesto));
        $response->assertSessionHasErrors('error');

        $this->assertDatabaseHas('impuestos', ['id' => $impuesto->id, 'deleted_at' => null]);
    }

    public function test_admin_puede_eliminar_impuesto_sin_productos(): void
    {
        CompanyContext::setCompanyId($this->empresaA->id);

        $impuesto = Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Impuesto Temporal',
            'codigo' => 'TMP',
            'porcentaje' => 2.00,
            'tipo' => TipoImpuesto::OTRO,
            'por_defecto' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('impuestos.destroy', $impuesto));
        $response->assertRedirect(route('impuestos.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted($impuesto);
    }

    public function test_calculo_monto_impuesto_funciona_correctamente(): void
    {
        $iva19 = new Impuesto([
            'tipo' => TipoImpuesto::IVA,
            'porcentaje' => 19.00,
        ]);
        $this->assertEquals(19000.00, $iva19->calcularMonto(100000.00));

        $exento = new Impuesto([
            'tipo' => TipoImpuesto::EXENTO,
            'porcentaje' => 0.00,
        ]);
        $this->assertEquals(0.00, $exento->calcularMonto(100000.00));

        $excluido = new Impuesto([
            'tipo' => TipoImpuesto::EXCLUIDO,
            'porcentaje' => 0.00,
        ]);
        $this->assertEquals(0.00, $excluido->calcularMonto(100000.00));
    }

    public function test_producto_relacion_y_accessor_porcentaje_iva(): void
    {
        CompanyContext::setCompanyId($this->empresaA->id);

        $impuesto = Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'IVA 19%',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA,
            'por_defecto' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $prodConTax = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'impuesto_id' => $impuesto->id,
            'codigo' => 'PROD-1',
            'nombre' => 'Prod con Tax',
            'precio_compra' => 5000,
            'precio_venta' => 10000,
            'stock' => 10,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $prodSinTax = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'impuesto_id' => null,
            'codigo' => 'PROD-2',
            'nombre' => 'Prod sin Tax',
            'precio_compra' => 5000,
            'precio_venta' => 10000,
            'stock' => 10,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->assertEquals(19.00, $prodConTax->porcentaje_iva);
        $this->assertEquals(0.00, $prodSinTax->porcentaje_iva);
    }

    public function test_aislamiento_multitenant_empresa_a_no_puede_modificar_impuesto_empresa_b(): void
    {
        CompanyContext::setCompanyId($this->empresaB->id);

        $impuestoB = Impuesto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Impuesto Beta',
            'codigo' => 'BETA1',
            'porcentaje' => 10.00,
            'tipo' => TipoImpuesto::OTRO,
            'por_defecto' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Admin A intenta editar Impuesto B -> 404 (filtrado por BelongsToCompany) o 403
        $response = $this->actingAs($this->adminA)->get(route('impuestos.edit', $impuestoB));
        $this->assertContains($response->status(), [403, 404]);

        $responsePut = $this->actingAs($this->adminA)->put(route('impuestos.update', $impuestoB), [
            'nombre' => 'Hackeado por A',
            'codigo' => 'BETA1',
            'porcentaje' => 10.00,
            'tipo' => TipoImpuesto::OTRO->value,
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);
        $this->assertContains($responsePut->status(), [403, 404]);

        $responseDel = $this->actingAs($this->adminA)->delete(route('impuestos.destroy', $impuestoB));
        $this->assertContains($responseDel->status(), [403, 404]);
    }
}
