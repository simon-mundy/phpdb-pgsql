<?php

namespace Laminas\Db\Pgsql\Driver\Pgsql;

use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Exception;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Adapter\Profiler;
use Laminas\Db\Pgsql\Connection as PgSqlConnection;

use function get_resource_type;
use function is_array;
use function is_resource;
use function pg_execute;
use function pg_last_error;
use function pg_prepare;
use function preg_replace_callback;
use function sprintf;

class Statement implements StatementInterface, Profiler\ProfilerAwareInterface
{
    /** @var int */
    protected static int $statementIndex = 0;

    /** @var string */
    protected string $statementName = '';

    /** @var Pgsql */
    protected Pgsql $driver;

    /** @var Profiler\ProfilerInterface */
    protected Profiler\ProfilerInterface $profiler;

    /** @var resource */
    protected $pgsql;

    /** @var resource */
    protected $resource;

    /** @var string */
    protected string $sql;

    /** @var ParameterContainer */
    protected ParameterContainer $parameterContainer;

    /**
     * @return $this Provides a fluent interface
     */
    public function setDriver(Pgsql $driver): static
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return $this Provides a fluent interface
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler): static
    {
        $this->profiler = $profiler;
        return $this;
    }

    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler(): ?Profiler\ProfilerInterface
    {
        return $this->profiler;
    }

    /**
     * Initialize
     *
     * @param  resource $pgsql
     * @return void
     * @throws Exception\RuntimeException For invalid or missing postgresql connection.
     */
    public function initialize($pgsql): void
    {
        if (
            ! $pgsql instanceof PgSqlConnection
            && (
                ! is_resource($pgsql)
                || 'pgsql link' !== get_resource_type($pgsql)
            )
        ) {
            throw new Exception\RuntimeException(sprintf(
                '%s: Invalid or missing postgresql connection; received "%s"',
                __METHOD__,
                get_resource_type($pgsql)
            ));
        }

        $this->pgsql = $pgsql;
    }

    /**
     * Get resource
     *
     * @todo Implement this method
     * phpcs:ignore Squiz.Commenting.FunctionComment.InvalidNoReturn
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set sql
     *
     * @param string $sql
     * @return $this Provides a fluent interface
     */
    public function setSql($sql): static
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * Get sql
     *
     * @return string
     */
    public function getSql(): ?string
    {
        return $this->sql;
    }

    /**
     * Set parameter container
     *
     * @return $this Provides a fluent interface
     */
    public function setParameterContainer(ParameterContainer $parameterContainer): static
    {
        $this->parameterContainer = $parameterContainer;
        return $this;
    }

    /**
     * Get parameter container
     *
     * @return ParameterContainer
     */
    public function getParameterContainer(): ParameterContainer
    {
        return $this->parameterContainer;
    }

    /**
     * Prepare
     *
     * @param string $sql
     */
    public function prepare($sql = null): StatementInterface
    {
        $sql = $sql ?: $this->sql;

        $pCount = 1;
        $sql    = preg_replace_callback(
            '#\$\##',
            function () use (&$pCount) {
                return '$' . $pCount++;
            },
            $sql
        );

        $this->sql           = $sql;
        $this->statementName = 'statement' . ++static::$statementIndex;
        $this->resource      = pg_prepare($this->pgsql, $this->statementName, $sql);
    }

    /**
     * Is prepared
     *
     * @return bool
     */
    public function isPrepared(): bool
    {
        return isset($this->resource);
    }

    /**
     * Execute
     *
     * @param array|ParameterContainer $parameters
     * @throws Exception\InvalidQueryException
     * @return Result
     */
    public function execute(ParameterContainer|array $parameters = null): ?ResultInterface
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
