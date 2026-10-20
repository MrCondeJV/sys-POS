<?php

namespace Tests\Feature\AuditoriaNocturna;

use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoVenta;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoPago;
use App\Enums\TipoPersona;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificarIntegridadCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_comando_verificar_integridad_retorna_exito_en_estado_consistente(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id, 'es_principal' => true]);
        $user = User::factory()->create(['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal->id]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_persona' => TipoPersona::NATURAL,
            'tipo_documento' => TipoDocumentoIdentidad::CC,
            'numero_documento' => '1020304050',
            'razon_social' => 'Juan Integridad',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Cemento Gris 50kg',
            'codigo' => 'CEM-50',
            'precio_venta' => 32000,
            'stock' => 20,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $venta = Venta::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'numero_venta' => 'VEN-INT-001',
            'fecha' => now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 64000,
            'descuento' => 0,
            'impuesto' => 0,
            'total' => 64000,
            'pago_con' => 64000,
            'cambio' => 0,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        VentaDetalle::create([
            'empresa_id' => $empresa->id,
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => 32000,
            'costo_unitario' => 25000,
            'descuento' => 0,
            'impuesto_porcentaje' => 0,
            'impuesto_monto' => 0,
            'subtotal' => 64000,
            'total' => 64000,
        ]);

        $this->artisan('sistema:verificar-integridad')
            ->expectsOutputToContain('OK: Todos los invariantes de integridad')
            ->assertExitCode(0);
    }

    public function test_comando_verificar_integridad_detecta_anomalias_en_ventas_stock_y_cartera(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id, 'es_principal' => true]);
        $user = User::factory()->create(['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal->id]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_persona' => TipoPersona::NATURAL,
            'tipo_documento' => TipoDocumentoIdentidad::CC,
            'numero_documento' => '99887766',
            'razon_social' => 'Cliente Descuadre',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Anomalía 1: Producto con stock negativo
        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Producto Stock Negativo',
            'codigo' => 'NEG-01',
            'precio_venta' => 10000,
            'stock' => -5,
            'stock_minimo' => 1,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Anomalía 2: Venta con cabecera != suma de detalles
        $venta = Venta::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'numero_venta' => 'VEN-DESC-001',
            'fecha' => now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 99000,
            'descuento' => 0,
            'impuesto' => 0,
            'total' => 99000,
            'pago_con' => 99000,
            'cambio' => 0,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        VentaDetalle::create([
            'empresa_id' => $empresa->id,
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => 10000,
            'costo_unitario' => 5000,
            'descuento' => 0,
            'impuesto_porcentaje' => 0,
            'impuesto_monto' => 0,
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        // Anomalía 3: Cuenta por cobrar descuadrada
        CuentaPorCobrar::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'cliente_id' => $cliente->id,
            'numero_documento' => 'CXC-DESC-01',
            'concepto' => 'Descuadre prueba',
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(15),
            'monto_total' => 50000,
            'monto_pagado' => 10000,
            'saldo_pendiente' => 15000, // Debería ser 40000
            'estado' => EstadoCuentaCobrar::PARCIAL,
        ]);

        $this->artisan('sistema:verificar-integridad')
            ->expectsOutputToContain('Se detectaron 3 anomalía(s) de integridad')
            ->assertExitCode(1);
    }
}
