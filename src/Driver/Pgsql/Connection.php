<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Driver\Pgsql;

use PgSql\Connection as PgSqlConnection;
use PhpDb\Adapter\Driver\AbstractConnection;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverAwareInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Exception;
use PhpDb\ResultSet\ResultSetInterface;

use function array_filter;
use function http_build_query;
use function is_array;
use function is_resource;
use function pg_connect;
use function pg_fetch_result;
use function pg_last_error;
use function pg_query;
use function pg_set_client_encoding;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function str_replace;
use function urldecode;

use const PGSQL_CONNECT_FORCE_NEW;

class Connection extends AbstractConnection implements DriverAwareInterface
{
    /** @var Pgsql */
    protected $driver;

    /** @var null|int PostgreSQL connection type */
    protected ?int $type = null;

    public function __construct(PgSqlConnection|array|null $connectionInfo = null)
    {
        if (is_array($connectionInfo)) {
            $this->setConnectionParameters($connectionInfo);
        } elseif ($connectionInfo instanceof PgSqlConnection || is_resource($connectionInfo)) {
            $this->setResource($connectionInfo);
        }
    }

    public function setResource(PgSqlConnection $resource): ConnectionInterface
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * old param type hint Pgsql $driver
     */
    public function setDriver(DriverInterface $driver): DriverAwareInterface
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return $this Provides a fluent interface
     */
    public function setType(?int $type): static
    {
        $invalidConectionType = $type !== PGSQL_CONNECT_FORCE_NEW;
        if ($invalidConectionType) {
            throw new Exception\InvalidArgumentException(
                'Connection type is not valid. (See: https://php.net/manual/en/function.pg-connect.php)'
            );
        }
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return null|string
     */
    public function getCurrentSchema(): bool|string
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        $result = pg_query($this->resource, 'SELECT CURRENT_SCHEMA AS "currentschema"');
        if ($result === false) {
            return false;
        }

        return pg_fetch_result($result, 0, 'currentschema');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException On failure.
     */
    public function connect(): static
    {
        if ($this->resource instanceof PgSqlConnection) {
            return $this;
        }

        $connection = $this->getConnectionString();
        set_error_handler(function ($number, $string) {
            throw new Exception\RuntimeException(
                self::class . '::connect: Unable to connect to database',
                $number ?? 0,
                new Exception\ErrorException($string, $number ?? 0)
            );
        });
        try {
            $this->resource = pg_connect($connection);
        } finally {
            restore_error_handler();
        }

        if ($this->resource === false) {
            throw new Exception\RuntimeException(sprintf(
                '%s: Unable to connect to database',
                __METHOD__
            ));
        }

        if (! empty($this->connectionParameters['charset'])) {
            if (pg_set_client_encoding($this->resource, $this->connectionParameters['charset']) === -1) {
                throw new Exception\RuntimeException(sprintf(
                    "%s: Unable to set client encoding '%s'",
                    __METHOD__,
                    $this->connectionParameters['charset']
                ));
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected(): bool
    {
        return $this->resource instanceof PgSqlConnection;
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect(): static
    {
        // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFallbackGlobalName
        pg_close($this->resource);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction(): static
    {
        if ($this->inTransaction()) {
            throw new Exception\RuntimeException('Nested transactions are not supported');
        }

        if (! $this->isConnected()) {
            $this->connect();
        }

        pg_query($this->resource, 'BEGIN');
        $this->inTransaction = true;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): static
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        if (! $this->inTransaction()) {
            return $this; // We ignore attempts to commit non-existing transaction
        }

        pg_query($this->resource, 'COMMIT');
        $this->inTransaction = false;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function rollback(): static
    {
        if (! $this->isConnected()) {
            throw new Exception\RuntimeException('Must be connected before you can rollback');
        }

        if (! $this->inTransaction()) {
            throw new Exception\RuntimeException('Must call beginTransaction() before you can rollback');
        }

        pg_query($this->resource, 'ROLLBACK');
        $this->inTransaction = false;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidQueryException
     * @return resource|ResultSetInterface
     */
    public function execute($sql): ResultInterface
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        $this->profiler?->profilerStart($sql);

        $resultResource = pg_query($this->resource, $sql);

        $this->profiler?->profilerFinish();

        // if the returnValue is something other than a pg result resource, bypass wrapping it
        if ($resultResource === false) {
            throw new Exception\InvalidQueryException(pg_last_error($this->resource));
        }

        return $this->driver->createResult($resultResource);
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getLastGeneratedValue($name = null): bool|int|string|null
    {
        if ($name === null) {
            return null;
        }
        $result = pg_query(
            $this->resource,
            'SELECT CURRVAL(\'' . str_replace('\'', '\\\'', $name) . '\') as "currval"'
        );

        return pg_fetch_result($result, 0, 'currval');
    }

    /**
     * Get Connection String
     */
    private function getConnectionString(): string
    {
        $connectionParameters = array_filter((new PgsqlConfig())($this->connectionParameters));

        return urldecode(http_build_query($connectionParameters, '', ' '));
    }
}
