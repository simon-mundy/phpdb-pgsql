<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql;

use Override;
use PDO;
use PgSql\Connection as PgSqlConnection;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\Pgsql\Pgsql;
use PhpDb\Adapter\Exception;
use PhpDb\Adapter\Pgsql\Driver\Pdo\Driver as PdoDriver;
use PhpDb\Adapter\Platform\AbstractPlatform;
use PhpDb\Sql\Platform\Platform as SqlPlatformDecorator;
use PhpDb\Sql\Platform\PlatformDecoratorInterface;

use function get_resource_type;
use function implode;
use function in_array;
use function is_resource;
use function pg_escape_string;
use function str_replace;

class AdapterPlatform extends AbstractPlatform
{
    public final const PLATFORM_NAME = 'PostgreSQL';
    /**
     * Overrides value from AbstractPlatform to use proper escaping for Postgres
     *
     * @var string
     */
    protected $quoteIdentifierTo = '""';

    /** @var string[] */
    private $knownPgsqlResources = [
        'pgsql link',
        'pgsql link persistent',
    ];

    public function __construct(
        private readonly DriverInterface|PdoDriverInterface|PDO $driver,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getName(): string
    {
        return self::PLATFORM_NAME;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function quoteIdentifierChain($identifierChain): string
    {
        return '"' . implode('"."', (array) str_replace('"', '""', $identifierChain)) . '"';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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
    #[Override]
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

    #[Override]
    public function getSqlPlatformDecorator(): PlatformDecoratorInterface
    {
        return new SqlPlatformDecorator($this);
    }
}
