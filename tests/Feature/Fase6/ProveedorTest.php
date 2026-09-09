<?php

namespace Tests\Feature\Fase6;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Models\Empresa;
use App\Models\Proveedor;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProveedorTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;

    protected Empresa $empresaB;

    protected User $adminA;

    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Empresa Alfa S.A.S.']);
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Empresa Beta S.A.S.']);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->adminB = User::factory()->create(['empresa_id' => $this->empresaB->id]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_admin_puede_ver_listado_proveedores_de_su_empresa_solamente(): void
    {
        $provA = Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Proveedor Alfa Ltda',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900111222-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $provB = Proveedor::create([
            'empresa_id' => $this->empresaB->id,
            'razon_social' => 'Proveedor Beta S.A.',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900333444-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('proveedores.index'));

        $response->assertStatus(200);
        $response->assertSee('Proveedor Alfa Ltda');
        $response->assertDontSee('Proveedor Beta S.A.');
    }

    public function test_admin_puede_crear_proveedor_con_datos_validos(): void
    {
        $payload = [
            'razon_social' => 'Inversiones Comerciales SAS',
            'nombre_contacto' => 'Laura Morales',
            'tipo_documento' => 'NIT',
            'numero_documento' => '901555666-7',
            'telefono' => '3201234567',
            'email' => 'contacto@inversiones.com',
            'direccion' => 'Cra 50 # 80-20',
            'ciudad' => 'Medellín',
            'departamento' => 'Antioquia',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->adminA)->post(route('proveedores.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('proveedores', [
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Inversiones Comerciales SAS',
            'numero_documento' => '901555666-7',
        ]);
    }

    public function test_no_se_puede_crear_proveedor_con_numero_documento_duplicado_en_misma_empresa(): void
    {
        Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Proveedor Existente',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900999888-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $payload = [
            'razon_social' => 'Otro Proveedor Con Mismo NIT',
            'tipo_documento' => 'NIT',
            'numero_documento' => '900999888-1',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->adminA)->post(route('proveedores.store'), $payload);

        $response->assertSessionHasErrors('numero_documento');
    }

    public function test_se_permite_mismo_numero_documento_en_distintas_empresas(): void
    {
        Proveedor::create([
            'empresa_id' => $this->empresaB->id,
            'razon_social' => 'Proveedor Global en Empresa B',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900999888-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $payload = [
            'razon_social' => 'Proveedor Global en Empresa A',
            'tipo_documento' => 'NIT',
            'numero_documento' => '900999888-1',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->adminA)->post(route('proveedores.store'), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('proveedores', [
            'empresa_id' => $this->empresaA->id,
            'numero_documento' => '900999888-1',
            'razon_social' => 'Proveedor Global en Empresa A',
        ]);
    }

    public function test_admin_puede_actualizar_proveedor_de_su_empresa(): void
    {
        $prov = Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Razón Original',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '800123456-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('proveedores.update', $prov), [
            'razon_social' => 'Razón Modificada S.A.S.',
            'tipo_documento' => 'NIT',
            'numero_documento' => '800123456-1',
            'estado' => 'ACTIVO',
            'telefono' => '3119998877',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('proveedores', [
            'id' => $prov->id,
            'razon_social' => 'Razón Modificada S.A.S.',
            'telefono' => '3119998877',
        ]);
    }

    public function test_admin_no_puede_actualizar_proveedor_de_otra_empresa(): void
    {
        $provB = Proveedor::create([
            'empresa_id' => $this->empresaB->id,
            'razon_social' => 'Proveedor Ajeno',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '700123456-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('proveedores.update', $provB), [
            'razon_social' => 'Intento Hackeo',
            'tipo_documento' => 'NIT',
            'numero_documento' => '700123456-1',
            'estado' => 'ACTIVO',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_puede_eliminar_proveedor_de_su_empresa(): void
    {
        $prov = Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Proveedor a Borrar',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '600123456-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('proveedores.destroy', $prov));

        $response->assertRedirect();
        $this->assertSoftDeleted('proveedores', ['id' => $prov->id]);
    }

    public function test_admin_no_puede_eliminar_proveedor_de_otra_empresa(): void
    {
        $provB = Proveedor::create([
            'empresa_id' => $this->empresaB->id,
            'razon_social' => 'Proveedor Ajeno a Borrar',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '500123456-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('proveedores.destroy', $provB));

        $response->assertStatus(403);
    }
}
