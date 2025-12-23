<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Pgsql\Pdo;

use Override;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Pgsql\AdapterPlatform;
use PhpDb\Adapter\Pgsql\Pdo\Connection;
use PhpDb\Adapter\Pgsql\Pdo\Driver;

use function getenv;
use function is_string;
use function strtolower;

trait SetupTrait
{
    protected ?AdapterInterface $adapter;
    protected ?string $hostname = 'localhost';

    #[Override]
    protected function setUp(): void
    {
        if (
            ! is_string((string) getenv('TESTS_PHPDB_ADAPTER_PGSQL'))
            || strtolower(getenv('TESTS_PHPDB_ADAPTER_PGSQL')) !== 'true'
        ) {
            $this->markTestSkipped('pdo_pgsql integration tests are not enabled!');
        }

        $connectionParams = [
            'driver'   => 'pdo_pgsql',
            'database' => (string) getenv('TESTS_PHPDB_ADAPTER_PGSQL_DATABASE'),
            'hostname' => (string) getenv('TESTS_PHPDB_ADAPTER_PGSQL_HOSTNAME'),
            'username' => (string) getenv('TESTS_PHPDB_ADAPTER_PGSQL_USERNAME'),
            'password' => (string) getenv('TESTS_PHPDB_ADAPTER_PGSQL_PASSWORD'),
        ];

        $pdoDriver = new Driver(
            new Connection($connectionParams),
            new Statement(),
            new Result()
        );

        $this->adapter = new Adapter(
            $pdoDriver,
            new AdapterPlatform($pdoDriver)
        );

        $this->hostname = (string) getenv('TESTS_PHPDB_ADAPTER_PGSQL_HOSTNAME');
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }
}
