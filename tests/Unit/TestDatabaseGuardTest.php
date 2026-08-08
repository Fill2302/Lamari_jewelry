<?php

namespace Tests\Unit;

use Illuminate\Foundation\Application;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\TestDatabaseGuard;

final class TestDatabaseGuardTest extends TestCase
{
    public function test_it_accepts_only_the_in_memory_testing_database(): void
    {
        TestDatabaseGuard::assertIsolated($this->application('testing', 'sqlite', ':memory:'));

        $this->addToAssertionCount(1);
    }

    #[DataProvider('unsafeConfigurations')]
    public function test_it_blocks_unsafe_database_configurations(
        string $environment,
        string $connection,
        string $database,
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsafe test database configuration blocked');

        TestDatabaseGuard::assertIsolated($this->application($environment, $connection, $database));
    }

    public static function unsafeConfigurations(): array
    {
        return [
            'staging environment' => ['staging', 'sqlite', ':memory:'],
            'production environment' => ['production', 'sqlite', ':memory:'],
            'staging sqlite file' => ['testing', 'sqlite', '/var/www/lamari/database/database.sqlite'],
            'mysql database' => ['testing', 'mysql', 'lamari'],
        ];
    }

    private function application(string $environment, string $connection, string $database): Application
    {
        $app = new Application(dirname(__DIR__, 2));
        $app->instance('env', $environment);
        $app->instance('config', new \Illuminate\Config\Repository([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => ['database' => $database],
                ],
            ],
        ]));

        return $app;
    }
}
