<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql;

use Override;
use PDO;
use PgSql\Connection as PgSqlConnection;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\AbstractPlatform;
use PhpDb\Adapter\Platform\SequenceCapableInterface;
use PhpDb\Sql\Strategy\SqlStrategyInterface;

use function implode;
use function is_resource;
use function pg_escape_string;
use function str_replace;

class AdapterPlatform extends AbstractPlatform implements SequenceCapableInterface
{
    /**
     * Overrides value from AbstractPlatform to use proper escaping for Postgres
     */
    protected string $quoteIdentifierTo = '""';

    public function __construct(
        private readonly DriverInterface|PdoDriverInterface|PDO $driver,
        ?SqlStrategyInterface $sqlStrategy = null,
    ) {
        $this->sqlStrategy = $sqlStrategy;
    }

    #[Override]
    protected function createDefaultSqlStrategy(): SqlStrategyInterface
    {
        return new Sql\PgsqlStrategy();
    }

    public function getNextSequenceValueSql(string $sequenceName): string
    {
        return 'SELECT NEXTVAL(\'"' . $sequenceName . '"\')';
    }

    public function getCurrentSequenceValueSql(string $sequenceName): string
    {
        return 'SELECT CURRVAL(\'"' . $sequenceName . '"\')';
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
        /** @var PgSqlConnection|string $resource */
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
}
