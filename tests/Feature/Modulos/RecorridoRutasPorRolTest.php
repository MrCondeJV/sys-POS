<?php

namespace Tests\Feature\Modulos;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Recorre TODAS las rutas GET de la aplicación con cada rol y con datos reales.
 * Ninguna puede responder con error de servidor: lo esperado es 200, una
 * redirección o un 403/404 cuando el rol no tiene permiso.
 */
class RecorridoRutasPorRolTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    /** Rutas que no son pantallas navegables o dependen de servicios externos. */
    private const EXCLUIDAS = [
        'login', 'logout', 'suscripcion.retorno', 'suscripcion.checkout', 'notificaciones.conteo',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
    }

    /** Valor para cada parámetro de ruta, con excepciones según el nombre de la ruta. */
    private function parametros(Route $route): ?array
    {
        $nombre = $route->getName();
        $valores = [
            'venta' => $this->ventaContado->id,
            'producto' => $this->producto->id,
            'cliente' => $this->cliente->id,
            'cuenta' => $this->cuenta->id,
            'pago' => $this->cuenta->pagos()->value('id'),
            'abono' => $this->cuenta->pagos()->value('id'),
            'compra' => $this->compra->id,
            'devolucion' => $this->devolucion->id,
            'documento' => str_starts_with($nombre, 'facturacion-electronica.')
                ? $this->documentoElectronico->id
                : $this->ventaCredito->documentoVenta->id,
            'traslado' => $this->traslado->id,
            'listaPrecio' => $this->lista->id,
            'impuesto' => $this->impuesto->id,
            'resolucione' => $this->resolucion->id,
            'caja' => $this->sesionCaja->caja_id,
            'sesion' => $this->sesionCaja->id,
            'auditoria' => Auditoria::query()->value('id'),
            'usuario' => $this->cajero->id,
            'empresa' => $this->empresa->id,
            'plan' => \App\Models\Plan::query()->value('id'),
        ];

        $params = [];
        foreach ($route->parameterNames() as $p) {
            if (! isset($valores[$p])) {
                return null;
            }
            $params[$p] = $valores[$p];
        }

        return $params;
    }

    private function rutasGet(): array
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $r) => in_array('GET', $r->methods()) && $r->getName()
                && in_array('web', $r->gatherMiddleware())
                && ! str_starts_with($r->uri(), '_')
                && ! str_starts_with($r->uri(), 'livewire')
                && ! in_array($r->getName(), self::EXCLUIDAS))
            ->values()
            ->all();
    }

    private function usuarioConRol(RolSistema $rol): User
    {
        $usuario = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'name' => 'Usuario ' . $rol->value,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($this->empresa->id);
        $usuario->assignRole($rol->value);

        return $usuario;
    }

    private function recorrer(User $usuario, string $etiqueta): void
    {
        $errores = [];
        $sinParametros = [];

        foreach ($this->rutasGet() as $route) {
            $params = $this->parametros($route);
            if ($params === null) {
                $sinParametros[] = $route->getName();
                continue;
            }

            $response = $this->actingAs($usuario)->get(route($route->getName(), $params));

            $status = $response->baseResponse->getStatusCode();
            if ($status >= 500) {
                $mensaje = $response->exception ? class_basename($response->exception) . ': ' . mb_substr($response->exception->getMessage(), 0, 160) : '';
                $errores[] = "{$route->getName()} => {$status} {$mensaje}";
            }
        }

        $this->assertSame([], $sinParametros, "Rutas sin valor de prueba para sus parámetros: " . implode(', ', $sinParametros));
        $this->assertSame([], $errores, "Errores de servidor con rol {$etiqueta}:\n" . implode("\n", $errores));
    }

    public function test_admin_empresa_recorre_todas_las_rutas_sin_errores(): void
    {
        $this->recorrer($this->admin, 'ADMIN_EMPRESA');
    }

    public function test_admin_sucursal_recorre_todas_las_rutas_sin_errores(): void
    {
        $this->recorrer($this->usuarioConRol(RolSistema::ADMIN_SUCURSAL), 'ADMIN_SUCURSAL');
    }

    public function test_cajero_recorre_todas_las_rutas_sin_errores(): void
    {
        $this->recorrer($this->cajero, 'CAJERO');
    }

    public function test_vendedor_recorre_todas_las_rutas_sin_errores(): void
    {
        $this->recorrer($this->usuarioConRol(RolSistema::VENDEDOR), 'VENDEDOR');
    }

    public function test_contador_recorre_todas_las_rutas_sin_errores(): void
    {
        $this->recorrer($this->usuarioConRol(RolSistema::CONTADOR), 'CONTADOR');
    }

    public function test_super_admin_gestionando_empresa_recorre_todas_las_rutas_sin_errores(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        $this->assertTrue($superAdmin->isSuperAdmin());

        $this->actingAs($superAdmin)->post(route('empresas.seleccionar'), ['empresa_id' => $this->empresa->id]);

        $this->recorrer($superAdmin, 'SUPER_ADMIN con empresa');
    }

    public function test_super_admin_sin_empresa_recorre_todas_las_rutas_sin_errores(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        $this->assertTrue($superAdmin->isSuperAdmin());
        \App\Support\Tenancy\CompanyContext::clear();
        \App\Support\Tenancy\BranchContext::clear();

        $this->recorrer($superAdmin, 'SUPER_ADMIN sin empresa');
    }
}
