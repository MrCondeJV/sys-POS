<?php

namespace Tests\Feature\Fase2;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserTenancyAndPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_usuario_solo_puede_ver_su_propia_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $userA = User::factory()->create(['empresa_id' => $empresaA->id]);

        $this->actingAs($userA);

        $this->assertTrue(Gate::allows('view', $empresaA));
        $this->assertFalse(Gate::allows('view', $empresaB));
    }

    public function test_usuario_no_puede_modificar_otra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $userA = User::factory()->create(['empresa_id' => $empresaA->id]);

        $this->actingAs($userA);

        $this->assertFalse(Gate::allows('update', $empresaB));
    }

    public function test_usuario_puede_ver_sucursal_de_su_empresa_pero_no_de_otra(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $sucursalA = Sucursal::factory()->create(['empresa_id' => $empresaA->id]);
        $sucursalB = Sucursal::factory()->create(['empresa_id' => $empresaB->id]);

        $userA = User::factory()->create(['empresa_id' => $empresaA->id]);

        $this->actingAs($userA);

        $this->assertTrue(Gate::allows('view', $sucursalA));
        $this->assertFalse(Gate::allows('view', $sucursalB));
    }

    public function test_middleware_set_company_context_establece_tenant_en_peticiones_autenticadas(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $this->assertEquals($empresa->id, CompanyContext::getId());
    }
}
