<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Pgsql\Driver\Pdo;

use Override;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Pgsql\Driver\Pdo\Driver;
use PhpDb\Adapter\Pgsql\Driver\Pdo\Connection;
use PhpDb\Adapter\Pgsql\Platform\Postgresql;

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

        $pdoDriver = new Pdo(
        new Connection($connectionParams),
        new Statement(),
        new Result()
        );

        $this->adapter = new Adapter(
            $pdoDriver,
            new Postgresql($pdoDriver)
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
