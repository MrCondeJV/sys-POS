<?php

namespace Tests\Feature\Fase7;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoPersona;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;

    protected Empresa $empresaB;

    protected User $adminA;

    protected User $adminB;

    protected User $cajeroA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Empresa Alfa S.A.S.']);
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Empresa Beta S.A.S.']);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        // Admin Empresa A
        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Cajero Empresa A
        $this->cajeroA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        // Admin Empresa B
        $this->adminB = User::factory()->create(['empresa_id' => $this->empresaB->id]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get(route('clientes.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_puede_ver_listado_clientes_y_metricas(): void
    {
        $clienteA = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Carlos Andrés Mendoza',
            'numero_documento' => '1045987123',
        ]);

        $response = $this->actingAs($this->adminA)->get(route('clientes.index'));

        $response->assertOk();
        $response->assertSee('Carlos Andrés Mendoza');
        $response->assertSee('1045987123');
        $response->assertSee('Catálogo de Clientes');
    }

    public function test_aislamiento_multitenant_estricto_no_muestra_clientes_de_otra_empresa(): void
    {
        $clienteA = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Cliente Exclusivo Alfa',
            'numero_documento' => '111111111',
        ]);

        $clienteB = Cliente::factory()->for($this->empresaB)->create([
            'razon_social' => 'Cliente Secreto Beta',
            'numero_documento' => '999999999',
        ]);

        // Empresa A consulta
        $responseA = $this->actingAs($this->adminA)->get(route('clientes.index'));
        $responseA->assertOk();
        $responseA->assertSee('Cliente Exclusivo Alfa');
        $responseA->assertDontSee('Cliente Secreto Beta');

        // Empresa B consulta
        $responseB = $this->actingAs($this->adminB)->get(route('clientes.index'));
        $responseB->assertOk();
        $responseB->assertSee('Cliente Secreto Beta');
        $responseB->assertDontSee('Cliente Exclusivo Alfa');
    }

    public function test_usuario_puede_crear_cliente_persona_natural(): void
    {
        $datos = [
            'tipo_persona' => TipoPersona::NATURAL->value,
            'tipo_documento' => TipoDocumentoIdentidad::CC->value,
            'numero_documento' => '1033445566',
            'razon_social' => 'Andrés Felipe Morales',
            'nombre_comercial' => null,
            'telefono' => '3012345678',
            'email' => 'andres.morales@correo.com',
            'direccion' => 'Calle 10 # 20-30',
            'ciudad' => 'Coveñas',
            'departamento' => 'Sucre',
            'cupo_credito' => 0,
            'plazo_dias' => 0,
            'estado' => EstadoGeneral::ACTIVO->value,
        ];

        $response = $this->actingAs($this->adminA)->post(route('clientes.store'), $datos);

        $response->assertRedirect(route('clientes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clientes', [
            'empresa_id' => $this->empresaA->id,
            'tipo_persona' => 'NATURAL',
            'tipo_documento' => 'CC',
            'numero_documento' => '1033445566',
            'razon_social' => 'Andrés Felipe Morales',
            'es_predeterminado' => false,
        ]);
    }

    public function test_usuario_puede_crear_cliente_persona_juridica_con_credito(): void
    {
        $datos = [
            'tipo_persona' => TipoPersona::JURIDICA->value,
            'tipo_documento' => TipoDocumentoIdentidad::NIT->value,
            'numero_documento' => '900987654-3',
            'razon_social' => 'Distribuciones del Sinú S.A.S.',
            'nombre_comercial' => 'Dis-Sinú',
            'telefono' => '3109876543',
            'email' => 'contacto@dissinu.com',
            'direccion' => 'Parque Industrial Bodega 12',
            'ciudad' => 'Montería',
            'departamento' => 'Córdoba',
            'cupo_credito' => 15000000,
            'plazo_dias' => 45,
            'estado' => EstadoGeneral::ACTIVO->value,
        ];

        $response = $this->actingAs($this->adminA)->post(route('clientes.store'), $datos);

        $response->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', [
            'empresa_id' => $this->empresaA->id,
            'tipo_persona' => 'JURIDICA',
            'tipo_documento' => 'NIT',
            'numero_documento' => '900987654-3',
            'razon_social' => 'Distribuciones del Sinú S.A.S.',
            'cupo_credito' => 15000000,
            'plazo_dias' => 45,
        ]);
    }

    public function test_no_se_puede_crear_cliente_con_documento_duplicado_en_la_misma_empresa(): void
    {
        Cliente::factory()->for($this->empresaA)->create([
            'numero_documento' => '1055667788',
        ]);

        $datos = [
            'tipo_persona' => TipoPersona::NATURAL->value,
            'tipo_documento' => TipoDocumentoIdentidad::CC->value,
            'numero_documento' => '1055667788',
            'razon_social' => 'Intento Duplicado',
            'estado' => EstadoGeneral::ACTIVO->value,
        ];

        $response = $this->actingAs($this->adminA)->post(route('clientes.store'), $datos);

        $response->assertSessionHasErrors('numero_documento');
    }

    public function test_distintas_empresas_pueden_registrar_mismo_numero_de_documento(): void
    {
        // Empresa A tiene este cliente
        Cliente::factory()->for($this->empresaA)->create([
            'numero_documento' => '88888888',
            'razon_social' => 'Cliente Alfa 88',
        ]);

        // Empresa B lo registra independientemente sin conflicto
        $datos = [
            'tipo_persona' => TipoPersona::NATURAL->value,
            'tipo_documento' => TipoDocumentoIdentidad::CC->value,
            'numero_documento' => '88888888',
            'razon_social' => 'Cliente Beta 88',
            'estado' => EstadoGeneral::ACTIVO->value,
        ];

        $response = $this->actingAs($this->adminB)->post(route('clientes.store'), $datos);

        $response->assertRedirect(route('clientes.index'));

        $this->assertEquals(2, Cliente::withoutGlobalScopes()->where('numero_documento', '88888888')->count());
    }

    public function test_usuario_puede_actualizar_datos_del_cliente(): void
    {
        $cliente = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Nombre Original',
            'telefono' => '3000000000',
        ]);

        $response = $this->actingAs($this->adminA)->put(route('clientes.update', $cliente), [
            'tipo_persona' => $cliente->tipo_persona->value,
            'tipo_documento' => $cliente->tipo_documento->value,
            'numero_documento' => $cliente->numero_documento,
            'razon_social' => 'Nombre Modificado',
            'telefono' => '3119999999',
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);

        $response->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'razon_social' => 'Nombre Modificado',
            'telefono' => '3119999999',
        ]);
    }

    public function test_cliente_consumidor_final_no_puede_ser_eliminado(): void
    {
        $consumidorFinal = Cliente::factory()->for($this->empresaA)->consumidorFinal()->create();

        $response = $this->actingAs($this->adminA)->delete(route('clientes.destroy', $consumidorFinal));

        // Debe rebotar o rechazar la eliminación
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('clientes', [
            'id' => $consumidorFinal->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cliente_regular_puede_ser_eliminado_logicamente(): void
    {
        $cliente = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Cliente Para Eliminar',
            'es_predeterminado' => false,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('clientes.destroy', $cliente));

        $response->assertRedirect(route('clientes.index'));
        $this->assertSoftDeleted('clientes', [
            'id' => $cliente->id,
        ]);
    }

    public function test_busqueda_y_filtros_en_catalogo_de_clientes(): void
    {
        Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Ferretería El Tornillo',
            'numero_documento' => '901999888',
            'tipo_persona' => TipoPersona::JURIDICA,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Pedro Nel Ospina',
            'numero_documento' => '70123456',
            'tipo_persona' => TipoPersona::NATURAL,
            'estado' => EstadoGeneral::INACTIVO,
        ]);

        // Filtro por término 'Tornillo'
        $resTerm = $this->actingAs($this->adminA)->get(route('clientes.index', ['buscar' => 'Tornillo']));
        $resTerm->assertSee('Ferretería El Tornillo');
        $resTerm->assertDontSee('Pedro Nel Ospina');

        // Filtro por tipo persona JURIDICA
        $resTipo = $this->actingAs($this->adminA)->get(route('clientes.index', ['tipo_persona' => 'JURIDICA']));
        $resTipo->assertSee('Ferretería El Tornillo');
        $resTipo->assertDontSee('Pedro Nel Ospina');

        // Filtro por estado INACTIVO
        $resEstado = $this->actingAs($this->adminA)->get(route('clientes.index', ['estado' => 'INACTIVO']));
        $resEstado->assertSee('Pedro Nel Ospina');
        $resEstado->assertDontSee('Ferretería El Tornillo');
    }

    public function test_usuario_de_empresa_a_no_puede_editar_cliente_de_empresa_b(): void
    {
        $clienteB = Cliente::factory()->for($this->empresaB)->create([
            'razon_social' => 'Cliente de Beta',
        ]);

        $response = $this->actingAs($this->adminA)->get(route('clientes.edit', $clienteB));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));

        $responseUpdate = $this->actingAs($this->adminA)->put(route('clientes.update', $clienteB), [
            'tipo_persona' => TipoPersona::NATURAL->value,
            'tipo_documento' => TipoDocumentoIdentidad::CC->value,
            'numero_documento' => $clienteB->numero_documento,
            'razon_social' => 'Hackeado',
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);
        $this->assertTrue(in_array($responseUpdate->getStatusCode(), [403, 404]));
    }
}
