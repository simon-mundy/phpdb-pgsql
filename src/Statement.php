<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql;

use Override;
use PgSql\Connection as PgSqlConnection;
use PgSql\Result as PgSqlResult;
use PhpDb\Adapter\Driver\DriverAwareInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Exception;
use PhpDb\Adapter\ParameterContainer;
use PhpDb\Adapter\Profiler\ProfilerAwareInterface;
use PhpDb\Adapter\Profiler\ProfilerInterface;

use function is_array;
use function pg_execute;
use function pg_last_error;
use function pg_prepare;
use function preg_replace_callback;

class Statement implements
    StatementInterface,
    DriverAwareInterface,
    ProfilerAwareInterface
{
    protected static int $statementIndex = 0;

    protected string $statementName = 'statement';

    protected Driver $driver;

    protected ProfilerInterface $profiler;

    protected PgSqlConnection $pgsql;

    protected PgSqlResult|false $resource;

    protected string $sql;

    protected ParameterContainer $parameterContainer;

    #[Override]
    public function setDriver(
        DriverInterface $driver
    ): StatementInterface&DriverAwareInterface&ProfilerAwareInterface {
        $this->driver = $driver;
        return $this;
    }

    #[Override]
    public function setProfiler(
        ProfilerInterface $profiler
    ): StatementInterface&DriverAwareInterface&ProfilerAwareInterface {
        $this->profiler = $profiler;
        return $this;
    }

    public function getProfiler(): (StatementInterface&ProfilerInterface)|null
    {
        return $this->profiler;
    }

    public function initialize(PgSqlConnection $pgsql): void
    {
        $this->pgsql = $pgsql;
    }

    #[Override]
    public function getResource(): PgSqlResult|false
    {
        return $this->resource;
    }

    #[Override]
    public function setSql(
        $sql
    ): StatementInterface&DriverAwareInterface&ProfilerAwareInterface {
        $this->sql = $sql;
        return $this;
    }

    #[Override]
    public function getSql(): ?string
    {
        return $this->sql;
    }

    #[Override]
    public function setParameterContainer(
        ParameterContainer $parameterContainer
    ): StatementInterface&DriverAwareInterface&ProfilerAwareInterface {
        $this->parameterContainer = $parameterContainer;
        return $this;
    }

    #[Override]
    public function getParameterContainer(): ParameterContainer
    {
        return $this->parameterContainer;
    }

    #[Override]
    public function prepare(
        ?string $sql = null
    ): StatementInterface&DriverAwareInterface&ProfilerAwareInterface {
        $sql = $sql ?? $this->sql;

        $pCount = 1;
        $sql    = preg_replace_callback(
            '#\$\##',
            function () use (&$pCount) {
                return '$' . $pCount++;
            },
            $sql
        );

        $this->sql           = $sql;
        $this->statementName .= ++static::$statementIndex;
        $this->resource      = pg_prepare($this->pgsql, $this->statementName, $sql);
        return $this;
    }

    #[Override]
    public function isPrepared(): bool
    {
        return isset($this->resource);
    }

    /**
     * Execute
     *
     * @throws Exception\InvalidQueryException
     */
    #[Override]
    public function execute(ParameterContainer|array|null $parameters = null): ?ResultInterface
    {
        if (! $this->isPrepared()) {
            $this->prepare();
        }

        /** START Standard ParameterContainer Merging Block */
        if (! $this->parameterContainer instanceof ParameterContainer) {
            if ($parameters instanceof ParameterContainer) {
                $this->parameterContainer = $parameters;
                $parameters               = null;
            } else {
                $this->parameterContainer = new ParameterContainer();
            }
        }

        if (is_array($parameters)) {
            $this->parameterContainer->setFromArray($parameters);
        }

        if ($this->parameterContainer->count() > 0) {
            $parameters = $this->parameterContainer->getPositionalArray();
        }
        /** END Standard ParameterContainer Merging Block */

        $this->profiler?->profilerStart($this);

        $resultResource = pg_execute($this->pgsql, $this->statementName, (array) $parameters);

        $this->profiler?->profilerFinish();

        if ($resultResource === false) {
            throw new Exception\InvalidQueryException(pg_last_error());
        }

        return $this->driver->createResult($resultResource);
    }
}
