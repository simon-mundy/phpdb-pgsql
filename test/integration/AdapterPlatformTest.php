<?php

namespace PhpDbIntegrationTest\Adapter\Pgsql;

use Override;
use PhpDb\Adapter\Driver\Pdo;
use PhpDb\Adapter\Pgsql\AdapterPlatform;
use PhpDb\Adapter\Pgsql\Connection as PgSqlConnection;
use PhpDb\Adapter\Pgsql\Driver;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function getenv;
use function is_resource;
use function pg_connect;

#[Group('integration')]
#[Group('integration-postgres')]
final class AdapterPlatformTest extends TestCase
{
    /** @var array<string, resource> */
    public array|\PDO $adapters = [];

    #[Override]
    protected function setUp(): void
    {
        if (! getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL')) {
            $this->markTestSkipped(self::class . ' integration tests are not enabled!');
        }
        if (extension_loaded('pgsql')) {
            $this->adapters['pgsql'] = pg_connect(
                'host=' . getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_HOSTNAME')
                    . ' dbname=' . getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_DATABASE')
                    . ' user=' . getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_USERNAME')
                    . ' password=' . getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_PASSWORD')
            );
        }
        if (extension_loaded('pdo')) {
            $this->adapters['pdo_pgsql'] = new \PDO(
                'pgsql:host=' . getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_HOSTNAME') . ';dbname='
                . getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_DATABASE'),
                getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_USERNAME'),
                getenv('TESTS_LAMINAS_DB_ADAPTER_PGSQL_PASSWORD')
            );
        }
    }

    /**
     * @return void
     */
    public function testQuoteValueWithPgsql()
    {
        if (
            ! isset($this->adapters['pgsql'])
            || (
                ! $this->adapters['pgsql'] instanceof PgSqlConnection
                && ! is_resource($this->adapters['pgsql'])
            )
        ) {
            $this->markTestSkipped('Postgres (pgsql) not configured in unit test configuration file');
        }
        $pgsql = new AdapterPlatform($this->adapters['pgsql']);
        $value = $pgsql->quoteValue('value');
        self::assertEquals('\'value\'', $value);

        $pgsql = new AdapterPlatform(new Driver(new PgSqlConnection($this->adapters['pgsql'])));
        $value = $pgsql->quoteValue('value');
        self::assertEquals('\'value\'', $value);
    }

    /**
     * @return void
     */
    public function testQuoteValueWithPdoPgsql()
    {
        if (! isset($this->adapters['pdo_pgsql']) || ! $this->adapters['pdo_pgsql'] instanceof \PDO) {
            $this->markTestSkipped('Postgres (PDO_PGSQL) not configured in unit test configuration file');
        }
        $pgsql = new Postgresql($this->adapters['pdo_pgsql']);
        $value = $pgsql->quoteValue('value');
        self::assertEquals('\'value\'', $value);

        $pgsql = new Postgresql(new Pdo\Pdo(new Pdo\Connection($this->adapters['pdo_pgsql'])));
        $value = $pgsql->quoteValue('value');
        self::assertEquals('\'value\'', $value);
    }
}
