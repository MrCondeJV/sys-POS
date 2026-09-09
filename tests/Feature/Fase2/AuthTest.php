<?php

namespace Tests\Feature\Fase2;

use App\Enums\EstadoGeneral;
use App\Models\Empresa;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_pantalla_de_login_se_renderiza_correctamente(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sistema POS Comercial');
        $response->assertSee('Correo Electrónico');
    }

    public function test_usuario_activo_puede_iniciar_sesion(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'email' => 'cajero@comercio.com',
            'password' => bcrypt('secret123'),
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->post('/login', [
            'email' => 'cajero@comercio.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $empresa = Empresa::factory()->create();
        User::factory()->inactivo()->create([
            'empresa_id' => $empresa->id,
            'email' => 'inactivo@comercio.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'inactivo@comercio.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_credenciales_invalidas_son_rechazadas(): void
    {
        $empresa = Empresa::factory()->create();
        User::factory()->create([
            'empresa_id' => $empresa->id,
            'email' => 'valido@comercio.com',
            'password' => bcrypt('password_correcto'),
        ]);

        $response = $this->post('/login', [
            'email' => 'valido@comercio.com',
            'password' => 'password_errado',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_proteccion_contra_fuerza_bruta_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'ataque@comercio.com',
                'password' => 'password_invalido',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'ataque@comercio.com',
            'password' => 'password_invalido',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsStringIgnoringCase('demasiados intentos', session('errors')->first('email'));
    }

    public function test_cierre_de_sesion_invalida_y_limpia_contexto(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user);
        CompanyContext::setCompanyId($empresa->id);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertFalse(CompanyContext::check());
    }
}
