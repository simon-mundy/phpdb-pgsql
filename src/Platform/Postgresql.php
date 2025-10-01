<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Platform;

use PDO;
use PgSql\Connection as PgSqlConnection;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\Pgsql;
use PhpDb\Adapter\Exception;
use PhpDb\Adapter\Pgsql\Driver\Pdo\Pdo as DriverPdo;
use PhpDb\Adapter\Platform\AbstractPlatform;
use PhpDb\Sql\Platform\Platform as SqlPlatformDecorator;
use PhpDb\Sql\Platform\PlatformDecoratorInterface;

use function get_resource_type;
use function implode;
use function in_array;
use function is_resource;
use function pg_escape_string;
use function str_replace;

class Postgresql extends AbstractPlatform
{
    /**
     * Overrides value from AbstractPlatform to use proper escaping for Postgres
     *
     * @var string
     */
    protected $quoteIdentifierTo = '""';

    /** @var null|resource|PDO|DriverPdo|Pgsql\Pgsql */
    protected $driver;

    /** @var string[] */
    private $knownPgsqlResources = [
        'pgsql link',
        'pgsql link persistent',
    ];

    public function __construct(PDO|Pgsql\Pgsql|DriverPdo|null $driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param Pgsql\Pgsql|DriverPdo|resource|PDO $driver
     * @throws Exception\InvalidArgumentException
     * @return $this Provides a fluent interface
     */
    public function setDriver($driver): static
    {
        if (
            $driver instanceof Pgsql\Pgsql
            || ($driver instanceof DriverPdo && $driver->getDatabasePlatformName() === 'Postgresql')
            || $driver instanceof PgSqlConnection // PHP 8.1+
            || (is_resource($driver) && in_array(get_resource_type($driver), $this->knownPgsqlResources, true))
            || ($driver instanceof PDO && $driver->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql')
        ) {
            $this->driver = $driver;

            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Pgsql or Postgresql PDO PhpDb\Adapter\Driver, pgsql link resource'
            . ' or Postgresql PDO instance'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'PostgreSQL';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain): string
    {
        return '"' . implode('"."', (array) str_replace('"', '""', $identifierChain)) . '"';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value): string
    {
        $quotedViaDriverValue = $this->quoteViaDriver($value);

        return $quotedViaDriverValue ?? 'E' . parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     *
     * @param scalar $value
     * @return string
     */
    public function quoteTrustedValue($value): string
    {
        $quotedViaDriverValue = $this->quoteViaDriver($value);

        if ($quotedViaDriverValue === null) {
            return 'E' . parent::quoteTrustedValue($value);
        }

        return $quotedViaDriverValue;
    }

    /**
     * @param string $value
     */
    protected function quoteViaDriver($value): ?string
    {
        $resource = $this->driver instanceof DriverInterface
            ? $this->driver->getConnection()->getResource()
            : $this->driver;

        if ($resource instanceof PgSqlConnection || is_resource($resource)) {
            return '\'' . pg_escape_string($resource, $value) . '\'';
        }

        if ($resource instanceof PDO) {
            return $resource->quote($value);
        }

        return null;
    }

    public function getSqlPlatformDecorator(): PlatformDecoratorInterface
    {
        return new SqlPlatformDecorator($this->driver);
    }
}
