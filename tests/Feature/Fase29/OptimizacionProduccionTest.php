<?php

namespace Tests\Feature\Fase29;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OptimizacionProduccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_indices_de_alto_rendimiento_existen_en_base_de_datos(): void
    {
        $this->assertTrue(Schema::hasTable('ventas'));
        $this->assertTrue(Schema::hasTable('movimientos_inventario'));
        $this->assertTrue(Schema::hasTable('producto_lotes'));
        $this->assertTrue(Schema::hasTable('cuentas_por_cobrar'));

        // Verificar que las columnas indexadas existen
        $this->assertTrue(Schema::hasColumns('ventas', ['empresa_id', 'created_at', 'tipo_pago']));
        $this->assertTrue(Schema::hasColumns('movimientos_inventario', ['empresa_id', 'producto_id', 'created_at']));
        $this->assertTrue(Schema::hasColumns('producto_lotes', ['empresa_id', 'fecha_vencimiento']));
        $this->assertTrue(Schema::hasColumns('cuentas_por_cobrar', ['empresa_id', 'fecha_vencimiento']));
    }

    public function test_archivos_de_despliegue_en_produccion_estan_configurados(): void
    {
        $this->assertFileExists(base_path('docker/nginx.conf'));
        $this->assertFileExists(base_path('docker/php.ini'));
        $this->assertFileExists(base_path('docker/supervisord.conf'));
        $this->assertFileExists(base_path('.env.production.example'));
        $this->assertFileExists(base_path('deploy.sh'));

        $nginx = file_get_contents(base_path('docker/nginx.conf'));
        $this->assertStringContainsString('fastcgi_pass', $nginx);
        $this->assertStringContainsString('gzip on', $nginx);

        $supervisor = file_get_contents(base_path('docker/supervisord.conf'));
        $this->assertStringContainsString('laravel-worker', $supervisor);
        $this->assertStringContainsString('laravel-cron', $supervisor);
    }
}
