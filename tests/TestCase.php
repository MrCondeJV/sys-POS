<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Una vista o controlador que lee un atributo inexistente debe fallar en los tests,
        // no mostrar null en silencio (así se ocultó el saldo en 0.00 del kardex).
        \Illuminate\Database\Eloquent\Model::preventAccessingMissingAttributes();
    }

    protected function tearDown(): void
    {
        \App\Support\Tenancy\CompanyContext::clear();
        \App\Support\Tenancy\BranchContext::clear();

        parent::tearDown();
    }
}
