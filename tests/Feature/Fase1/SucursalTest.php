<?php

namespace Tests\Feature\Fase1;

use App\Enums\EstadoGeneral;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SucursalTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_se_puede_crear_sucursal_asociada_a_empresa(): void
    {
        $empresa = Empresa::factory()->create();

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Sucursal Principal Poblado',
            'codigo' => 'SUC-001',
            'direccion' => 'Cra 43A # 1-50',
            'ciudad' => 'Medellín',
            'departamento' => 'Antioquia',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->assertDatabaseHas('sucursales', [
            'id' => $sucursal->id,
            'empresa_id' => $empresa->id,
            'nombre' => 'Sucursal Principal Poblado',
        ]);

        $this->assertEquals($empresa->id, $sucursal->empresa->id);
        $this->assertTrue($sucursal->isPrincipal());
    }

    public function test_no_se_puede_repetir_nombre_de_sucursal_en_la_misma_empresa(): void
    {
        $empresa = Empresa::factory()->create();

        Sucursal::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Sede Central',
        ]);

        $this->expectException(QueryException::class);

        Sucursal::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Sede Central',
        ]);
    }

    public function test_dos_empresas_diferentes_pueden_tener_sucursal_con_mismo_nombre(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $sucursalA = Sucursal::factory()->create([
            'empresa_id' => $empresaA->id,
            'nombre' => 'Sede Central',
        ]);

        $sucursalB = Sucursal::factory()->create([
            'empresa_id' => $empresaB->id,
            'nombre' => 'Sede Central',
        ]);

        $this->assertDatabaseHas('sucursales', ['id' => $sucursalA->id, 'empresa_id' => $empresaA->id]);
        $this->assertDatabaseHas('sucursales', ['id' => $sucursalB->id, 'empresa_id' => $empresaB->id]);
    }

    public function test_integridad_referencial_cascada_al_eliminar_empresa_en_bd(): void
    {
        $empresa = Empresa::factory()->create();
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id]);

        $this->assertDatabaseHas('sucursales', ['id' => $sucursal->id]);

        // Eliminación física forzada en BD para probar la foreign key ON DELETE CASCADE
        $empresa->forceDelete();

        $this->assertDatabaseMissing('sucursales', ['id' => $sucursal->id]);
    }

    public function test_aislamiento_multiempresa_mediante_company_context(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $sucursalA = Sucursal::factory()->create(['empresa_id' => $empresaA->id, 'nombre' => 'Sucursal A']);
        $sucursalB = Sucursal::factory()->create(['empresa_id' => $empresaB->id, 'nombre' => 'Sucursal B']);

        // Establecer contexto de Empresa A
        CompanyContext::setCompanyId($empresaA->id);

        $visiblesEmpresaA = Sucursal::all();
        $this->assertCount(1, $visiblesEmpresaA);
        $this->assertEquals($sucursalA->id, $visiblesEmpresaA->first()->id);
        $this->assertFalse($visiblesEmpresaA->contains('id', $sucursalB->id));

        // Establecer contexto de Empresa B
        CompanyContext::setCompanyId($empresaB->id);

        $visiblesEmpresaB = Sucursal::all();
        $this->assertCount(1, $visiblesEmpresaB);
        $this->assertEquals($sucursalB->id, $visiblesEmpresaB->first()->id);
        $this->assertFalse($visiblesEmpresaB->contains('id', $sucursalA->id));

        // Asignación automática de empresa_id al crear sucursal en contexto
        $nuevaSucursalB = Sucursal::create([
            'nombre' => 'Segunda Sucursal B',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->assertEquals($empresaB->id, $nuevaSucursalB->empresa_id);
    }
}
