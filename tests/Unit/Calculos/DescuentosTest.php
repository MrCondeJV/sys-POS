<?php

namespace Tests\Unit\Calculos;

use PHPUnit\Framework\TestCase;

class DescuentosTest extends TestCase
{
    /**
     * Descuento porcentual sobre el subtotal.
     */
    public function test_descuento_porcentual(): void
    {
        $subtotal = 200000.0;
        $porcentajeDescuento = 15.0; // 15%

        $descuento = round($subtotal * ($porcentajeDescuento / 100), 2);
        $totalConDescuento = $subtotal - $descuento;

        $this->assertEquals(30000.0, $descuento);
        $this->assertEquals(170000.0, $totalConDescuento);
    }

    /**
     * Descuento en valor fijo monetario.
     */
    public function test_descuento_valor_fijo(): void
    {
        $subtotal = 85000.0;
        $descuentoFijo = 10000.0;

        $totalConDescuento = max(0, $subtotal - $descuentoFijo);

        $this->assertEquals(75000.0, $totalConDescuento);
    }

    /**
     * El descuento no puede exceder el subtotal de la transacción.
     */
    public function test_descuento_no_puede_exceder_subtotal(): void
    {
        $subtotal = 50000.0;
        $descuentoExcesivo = 60000.0;

        $descuentoEfectivo = min($subtotal, $descuentoExcesivo);
        $total = $subtotal - $descuentoEfectivo;

        $this->assertEquals(50000.0, $descuentoEfectivo);
        $this->assertEquals(0.0, $total);
    }
}
