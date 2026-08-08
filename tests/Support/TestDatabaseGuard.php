<?php

namespace Tests\Support;

use Illuminate\Foundation\Application;
use RuntimeException;

final class TestDatabaseGuard
{
    public static function assertIsolated(Application $app): void
    {
        $environment = $app->environment();
        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($environment !== 'testing' || $connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(sprintf(
                'Unsafe test database configuration blocked (environment=%s, connection=%s, database=%s). Clear cached config and use the isolated in-memory SQLite database.',
                $environment,
                $connection,
                $database,
            ));
        }
    }
}
