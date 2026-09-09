<?php

namespace Tests\Unit\Calculos;

use PHPUnit\Framework\TestCase;

class ImpuestosTest extends TestCase
{
    /**
     * Cálculo de IVA 19% estándar sobre base imponible.
     */
    public function test_calculo_iva_19_por_ciento(): void
    {
        $base = 100000.0;
        $porcentaje = 19.0;
        $iva = round($base * ($porcentaje / 100), 2);
        $total = $base + $iva;

        $this->assertEquals(19000.0, $iva);
        $this->assertEquals(119000.0, $total);
    }

    /**
     * Cálculo de IVA 5% de tarifa diferencial.
     */
    public function test_calculo_iva_5_por_ciento(): void
    {
        $base = 50000.0;
        $porcentaje = 5.0;
        $iva = round($base * ($porcentaje / 100), 2);
        $total = $base + $iva;

        $this->assertEquals(2500.0, $iva);
        $this->assertEquals(52500.0, $total);
    }

    /**
     * Desglose de base e IVA cuando el precio viene con impuesto incluido.
     */
    public function test_desglose_precio_iva_incluido(): void
    {
        $precioFinal = 119000.0;
        $porcentaje = 19.0;

        $base = round($precioFinal / (1 + ($porcentaje / 100)), 2);
        $iva = round($precioFinal - $base, 2);

        $this->assertEquals(100000.0, $base);
        $this->assertEquals(19000.0, $iva);
    }

    /**
     * Producto exento o excluido (IVA 0%).
     */
    public function test_producto_tarifa_cero(): void
    {
        $base = 75000.0;
        $porcentaje = 0.0;
        $iva = round($base * ($porcentaje / 100), 2);

        $this->assertEquals(0.0, $iva);
        $this->assertEquals(75000.0, $base + $iva);
    }
}
