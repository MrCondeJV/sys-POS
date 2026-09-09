<?php

namespace Tests\Feature\Fase1;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_se_puede_crear_una_empresa_con_datos_validos(): void
    {
        $empresa = Empresa::create([
            'nombre_comercial' => 'Ferretería El Tornillo',
            'razon_social' => 'Ferretería El Tornillo S.A.S.',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'nit' => '900123456',
            'dv' => '1',
            'email' => 'contacto@eltornillo.co',
            'telefono' => '3001234567',
            'direccion' => 'Calle 10 # 20-30',
            'ciudad' => 'Medellín',
            'departamento' => 'Antioquia',
            'moneda' => 'COP',
            'simbolo_moneda' => '$',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'nit' => '900123456',
            'nombre_comercial' => 'Ferretería El Tornillo',
            'estado' => 'ACTIVO',
        ]);

        $this->assertTrue($empresa->isActiva());
    }

    public function test_nit_debe_ser_unico(): void
    {
        Empresa::factory()->create(['nit' => '900999888']);

        $this->expectException(QueryException::class);

        Empresa::factory()->create(['nit' => '900999888']);
    }

    public function test_empresa_puede_tener_multiples_sucursales(): void
    {
        $empresa = Empresa::factory()->create();

        $sucursal1 = Sucursal::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Sede Centro',
            'es_principal' => true,
        ]);

        $sucursal2 = Sucursal::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Sede Norte',
            'es_principal' => false,
        ]);

        $this->assertCount(2, $empresa->sucursales);
        $this->assertEquals($sucursal1->id, $empresa->sucursalPrincipal->id);
    }

    public function test_scope_activa_filtra_empresas_correctamente(): void
    {
        Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        Empresa::factory()->create(['estado' => EstadoGeneral::INACTIVO]);

        $activas = Empresa::activa()->get();

        $this->assertCount(1, $activas);
        $this->assertEquals(EstadoGeneral::ACTIVO, $activas->first()->estado);
    }

    public function test_soft_deletes_en_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $empresa->delete();

        $this->assertSoftDeleted('empresas', ['id' => $empresa->id]);
        $this->assertNull(Empresa::find($empresa->id));
        $this->assertNotNull(Empresa::withTrashed()->find($empresa->id));
    }
}
