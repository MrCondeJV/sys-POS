<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        \App\Support\Tenancy\CompanyContext::clear();
        \App\Support\Tenancy\BranchContext::clear();

        parent::tearDown();
    }
}
