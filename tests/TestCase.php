<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\TestDatabaseGuard;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        TestDatabaseGuard::assertIsolated($app);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram_orders.enabled' => false]);
    }
}
