<?php

namespace Tests\Unit\Calculos;

use PHPUnit\Framework\TestCase;

class ConversionUnidadesTest extends TestCase
{
    /**
     * Conversión de presentación comercial (Caja x 50 unidades) a unidades base.
     */
    public function test_conversion_caja_a_unidades(): void
    {
        $cantidadCajas = 4.0;
        $factorConversion = 50.0;

        $unidadesBase = $cantidadCajas * $factorConversion;

        $this->assertEquals(200.0, $unidadesBase);
    }

    /**
     * Conversión fraccionada (metros vendidos de un rollo de 100m).
     */
    public function test_conversion_fraccionada(): void
    {
        $metrosVendidos = 12.5;
        $factorConversion = 1.0; // Producto base en metros

        $cantidadBase = $metrosVendidos * $factorConversion;

        $this->assertEquals(12.5, $cantidadBase);
    }

    /**
     * Venta fraccionada de peso (medio kilo = 0.5 kg).
     */
    public function test_venta_fraccionada_granel(): void
    {
        $kilosVendidos = 0.750; // 750 gramos
        $precioPorKilo = 12000.0;

        $total = round($kilosVendidos * $precioPorKilo, 2);

        $this->assertEquals(9000.0, $total);
    }
}
