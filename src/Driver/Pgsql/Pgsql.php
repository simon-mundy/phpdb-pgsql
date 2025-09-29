<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Driver\Pgsql;

use Override;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverAwareInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Exception;
use PhpDb\Adapter\Pgsql\Connection as PgSqlConnection;
use PhpDb\Adapter\Pgsql\Driver\DatabasePlatformNameTrait;
use PhpDb\Adapter\Pgsql\Result as PgSqlResult;
use PhpDb\Adapter\Profiler\ProfilerAwareInterface;
use PhpDb\Adapter\Profiler\ProfilerInterface;

use function array_intersect_key;
use function array_merge;
use function extension_loaded;
use function is_string;

class Pgsql implements DriverInterface, ProfilerAwareInterface
{
    use DatabasePlatformNameTrait;

    protected ?ProfilerInterface $profiler = null;

    /** @var array */
    protected $options = [
        'buffer_results' => false,
    ];

    public function __construct(
        protected readonly ConnectionInterface&Connection $connection,
        protected readonly StatementInterface&Statement $statementPrototype,
        protected readonly ResultInterface $resultPrototype,
        array $options = []
    ) {
        $this->checkEnvironment();

        //todo: verify this usage
        $options = array_intersect_key(array_merge($this->options, $options), $this->options);

        if ($this->connection instanceof DriverAwareInterface) {
            $this->connection->setDriver($this);
        }
        if ($this->statementPrototype instanceof DriverAwareInterface) {
            $this->statementPrototype->setDriver($this);
        }
    }

    #[Override]
    public function setProfiler(ProfilerInterface $profiler): ProfilerAwareInterface
    {
        $this->profiler = $profiler;
        if ($this->connection instanceof ProfilerAwareInterface) {
            $this->connection->setProfiler($profiler);
        }

        if ($this->statementPrototype instanceof ProfilerAwareInterface) {
            $this->statementPrototype->setProfiler($profiler);
        }

        return $this;
    }

    #[Override]
    public function getProfiler(): ?ProfilerInterface
    {
        return $this->profiler;
    }

    #[Override]
    public function checkEnvironment(): bool
    {
        if (! extension_loaded('pgsql')) {
            throw new Exception\RuntimeException(
                'The PostgreSQL (pgsql) extension is required for this adapter but the extension is not loaded'
            );
        }
        return true;
    }

    #[Override]
    public function getConnection(): ConnectionInterface&Connection
    {
        return $this->connection;
    }

    /**
     * Create statement
     *
     * @param resource|string|null $sqlOrResource
     */
    #[Override]
    public function createStatement($sqlOrResource = null): StatementInterface&Statement
    {
        $statement = clone $this->statementPrototype;

        if (is_string($sqlOrResource)) {
            $statement->setSql($sqlOrResource);
        }

        if (! $this->connection->isConnected()) {
            $this->connection->connect();
        }

        $statement->initialize($this->connection->getResource());

        return $statement;
    }

    /**
     * Create result
     *
     * @param resource|PgSqlResult|PgSqlConnection $resource
     */
    #[Override]
    public function createResult($resource): ResultInterface&Result
    {
        /** @var Result $result */
        $result = clone $this->resultPrototype;
        $result->initialize($resource, $this->connection->getLastGeneratedValue());

        return $result;
    }

    public function getResultPrototype(): ResultInterface&Result
    {
        return $this->resultPrototype;
    }

    #[Override]
    public function getPrepareType(): string
    {
        return self::PARAMETERIZATION_POSITIONAL;
    }

    #[Override]
    public function formatParameterName(string $name, ?string $type = null): string
    {
        return '$#';
    }

    /**
     * Get last generated value
     */
    #[Override]
    public function getLastGeneratedValue(?string $name = null): int|string|false|null
    {
        return $this->connection->getLastGeneratedValue($name);
    }
}
